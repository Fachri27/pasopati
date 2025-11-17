<?php

if (! function_exists('seo')) {
    function seo(): \App\Services\SeoService
    {
        return app('seo');
    }
}
