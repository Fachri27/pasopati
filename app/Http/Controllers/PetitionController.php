<?php

namespace App\Http\Controllers;

use App\Http\Requests\SignPetitionRequest;
use App\Mail\PetitionVerificationMail;
use App\Models\Petition;
use App\Models\PetitionSignature;
use App\Services\ProfanityFilter;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PetitionController extends Controller
{
    public function index()
    {
        $locale = app()->getLocale();

        seo()->setLocale($locale)
            ->set('title', ['id' => 'Petisi', 'en' => 'Petitions'])
            ->set('description', ['id' => 'Tanda tangani petisi dan buat perubahan', 'en' => 'Sign petitions and make a change'])
            ->set('image', asset('img/image.png'))
            ->set('type', 'website');

        $petitions = Petition::with('translations')
            ->where('status', 'active')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->paginate(9);

        return view('front.petition.index', compact('petitions', 'locale'));
    }

    public function show(\Illuminate\Http\Request $request)
    {
        $slug = $request->route('slug');
        $locale = app()->getLocale();

        $petition = Petition::with(['translations', 'verifiedSignatures'])
            ->where('slug', $slug)
            ->firstOrFail();

        $trans = $petition->translation($locale);

        seo()->setLocale($locale)
            ->set('title', ['id' => $trans?->title ?? $petition->slug, 'en' => $trans?->title ?? $petition->slug])
            ->set('description', ['id' => Str::limit(strip_tags($trans?->description ?? ''), 160), 'en' => Str::limit(strip_tags($trans?->description ?? ''), 160)])
            ->set('image', $petition->cover_image ? asset('storage/' . $petition->cover_image) : asset('img/image.png'))
            ->set('type', 'article');

        return view('front.petition.show', compact('petition', 'locale'));
    }

    public function sign(SignPetitionRequest $request)
    {
        $slug = $request->route('slug');

        $petition = Petition::where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $existing = PetitionSignature::where('petition_id', $petition->id)
            ->where('email', $request->email)
            ->first();

        if ($existing) {
            if ($existing->is_verified) {
                return back()->with('info', __('Anda sudah menandatangani petisi ini.'));
            }

            $existing->update([
                'name' => $request->name,
                'city' => $request->city,
                'comment' => $request->comment,
                'ip_address' => $request->ip(),
                'verification_token' => Str::random(64),
            ]);

            $signature = $existing;
        } else {
            $profanity = app(ProfanityFilter::class);

            $signature = PetitionSignature::create([
                'petition_id' => $petition->id,
                'name' => $request->name,
                'email' => $request->email,
                'city' => $request->city,
                'comment' => $request->comment ? $profanity->filter($request->comment) : null,
                'ip_address' => $request->ip(),
                'verification_token' => Str::random(64),
            ]);
        }

        $verifyUrl = route('petition.verify', [
            'locale' => app()->getLocale(),
            'token' => $signature->verification_token,
        ]);

        Mail::to($signature->email)->queue(new PetitionVerificationMail($petition, $verifyUrl));

        return back()->with('success', __('Cek email Anda untuk verifikasi tanda tangan.'));
    }

    public function verify(string $token)
    {
        $signature = PetitionSignature::where('verification_token', $token)
            ->where('is_verified', false)
            ->firstOrFail();

        $signature->update([
            'is_verified' => true,
            'verification_token' => null,
        ]);

        $locale = app()->getLocale();
        $petition = Petition::with('translations')->findOrFail($signature->petition_id);

        return redirect()->route('petition.show', [
            'locale' => $locale,
            'slug' => $petition->slug,
        ])->with('success', __('Tanda tangan berhasil diverifikasi!'));
    }
}
