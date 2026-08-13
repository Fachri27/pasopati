<?php

namespace Tests\Feature;

use App\Models\Comment;
use Tests\TestCase;

/**
 * Render markdown body komentar → HTML aman (Comment::formatBody).
 * Fokus pada daftar poin & daftar bernomor, termasuk bersarang.
 */
class CommentFormatBodyTest extends TestCase
{
    public function test_bullet_list_renders_as_ul(): void
    {
        $body = "- satu\n- dua\n- tiga";

        $html = Comment::formatBody($body);

        $this->assertStringContainsString('<ul>', $html);
        $this->assertStringContainsString('<li>satu</li>', $html);
        $this->assertStringContainsString('<li>dua</li>', $html);
        $this->assertStringContainsString('<li>tiga</li>', $html);
        $this->assertStringContainsString('</ul>', $html);
        $this->assertStringNotContainsString('- satu', $html);
    }

    public function test_numbered_list_renders_as_ol(): void
    {
        $body = "1. pertama\n2. kedua\n3. ketiga";

        $html = Comment::formatBody($body);

        $this->assertStringContainsString('<ol>', $html);
        $this->assertStringContainsString('<li>pertama</li>', $html);
        $this->assertStringContainsString('<li>kedua</li>', $html);
        $this->assertStringContainsString('<li>ketiga</li>', $html);
        $this->assertStringContainsString('</ol>', $html);
    }

    public function test_nested_list_renders_nested_html(): void
    {
        $body = "- atas\n  - anak a\n  - anak b\n- bawah";

        $html = Comment::formatBody($body);

        // Anak bersarang di dalam <li> atas, bukan jadi sibling datar.
        $this->assertStringContainsString('<li>atas<ul>', $html);
        $this->assertStringContainsString('<li>anak a</li>', $html);
        $this->assertStringContainsString('<li>anak b</li>', $html);
        $this->assertStringContainsString('<li>bawah</li>', $html);
    }

    public function test_inline_formatting_inside_list_item(): void
    {
        $body = '- **tebal** dan _miring_';

        $html = Comment::formatBody($body);

        $this->assertStringContainsString('<li><strong>tebal</strong> dan <em>miring</em></li>', $html);
    }

    public function test_list_marker_in_middle_of_text_is_not_a_list(): void
    {
        // "1.5" tanpa spasi setelah titik bukan marker daftar.
        $html = Comment::formatBody('Harga 1.5 juta');

        $this->assertStringNotContainsString('<ol>', $html);
    }
}
