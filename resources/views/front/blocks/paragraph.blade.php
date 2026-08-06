@php $ptop = $data['mt'] ?? null; $pbot = $data['mb'] ?? null; @endphp
<div @if($ptop || $pbot) style="margin-top: {{ $ptop }}px; margin-bottom: {{ $pbot }}px;" @endif
     class="
      prose
      max-w-2xl mx-auto
      px-5
      poppins-regular

      md:text-md sm:text-base text-[16px]
      text-left

      prose-p:tracking-[0.020em]
      prose-p:my-[1em]

      prose-h2:text-[24px]
      prose-h2:mt-8 prose-h2:mb-4 prose-h2:font-bold

      prose-h3:text-[21px]
      prose-h3:mt-6 prose-h3:mb-3 prose-h3:font-semibold
    ">
    {!! $data['html'] ?? '' !!}
</div>
