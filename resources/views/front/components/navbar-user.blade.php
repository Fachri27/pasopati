<nav x-data="{ open: false }" class="bg-white shadow-md fixed top-0 left-0 w-full z-50 px-3">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center h-16">

            <a href="/" class="md:text-2xl text-xl font-bold text-red-600 uppercase text-bold">
                <img src="https://pasopati.id/img/logo_pasopati.png" alt="" class="img-logo md:w-[400px] w-[200px]"
                    id="logo-pasopati">
            </a>

            {{-- Menu Hamburger --}}
            <div class="flex items-center space-x-3">
                <form action="" method="get" class="md:flex hidden">
                    <input type="text" value="{{ $search ?? '' }}" placeholder="Pencarian..." name="search">
                    <div class="flex">
                        <button type="submit" class="bg-[#2B5343] p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-4 text-white">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </button>
                    </div>
                </form>
                <div class="flex items-right space-x-1 text-sm">
                    <a href="{{ route(Route::currentRouteName(), array_merge(Route::current()->parameters(), ['locale' => 'en'])) }}"
                        class="hover:text-green-900 {{ app()->getLocale() === 'en' ? 'font-bold text-red-600' : '' }}">EN</a>
                    <span>|</span>
                    <a href="{{ route(Route::currentRouteName(), array_merge(Route::current()->parameters(), ['locale' => 'id'])) }}"
                        class="hover:text-green-900 {{ app()->getLocale() === 'id' ? 'font-bold text-red-600' : '' }}">ID</a>
                </div>
                <button @click="open = !open" class="bg-[#2B5343] py-4 px-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>

            {{-- overlay --}}
            <div x-show="open" x-transition.opacity class="fixed inset-0 bg-black/50 z-50"
                style="display: none !important;">
            </div>

            {{-- Menu --}}
            <div x-show="open" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                class="fixed top-0 right-0 bg-white sm:w-[75vw] md:w-100 h-full z-50 p-6 overflow-y-auto" @click.away="open = false"
                style="display: none !important;">
                <div class="flex justify-end">
                    <button @click="open = false">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <ul class="mt-6 space-y-3">
                    <form action="" method="get" class="md:hidden flex w-full">
                        <input type="text" value="{{ $search ?? '' }}" placeholder="Pencarian..." name="search">
                        <div class="flex">
                            <button type="submit" class="bg-[#2B5343] p-3">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-4 text-white">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                </svg>
                            </button>
                        </div>
                    </form>
                    <li>
                        <a href="" class="block text-gray-800 font-semibold uppercase">Home</a>
                    </li>
                    <li class="border-b border-black">
                        <span class="uppercase text-sm font-semibold">expose</span>
                        <ul>
                            <li>
                                <span class="text-red-700 text-xl">•</span>
                                <a href="{{ route('artikel.expose', ['locale' => app()->getLocale(), 'expose_type' => 'deforestasi']) }}">
                                Deforestasi
                            </li>
                        </ul>
                        <ul>
                            <li>
                                <span class="text-red-700 text-xl">•</span>
                                <a href="{{ route('artikel.expose', ['locale' => app()->getLocale(), 'expose_type' => 'kebakaran']) }}">
                                Kebakaran
                            </li>
                        </ul>
                        <ul>
                            <li>
                                <span class="text-red-700 text-xl">•</span>
                               <a href="{{ route('artikel.expose', ['locale' => app()->getLocale(), 'expose_type' => 'pulp']) }}">
                                Pulp & paper
                            </li>
                        </ul>
                        <ul>
                            <li>
                                <span class="text-red-700 text-xl">•</span>
                                <a href="{{ route('artikel.expose', ['locale' => app()->getLocale(), 'expose_type' => 'mining']) }}">
                                Mining & energy
                            </li>
                        </ul>
                    </li>
                    <li class="border-b border-black">
                        <span class="uppercase font-semibold">Fellowship</span>

                        <ul>
                            @foreach ($yearPosts as $year => $posts)
                            @foreach ($posts as $post)
                            <li>
                                <span class="text-red-700 text-xl">•</span>

                                <a href="{{ route('fellowship.preview', [$locale, $post->slug]) }}">
                                    {{ $year }}
                                </a>
                            </li>
                            @endforeach
                            @endforeach
                        </ul>
                    </li>

                    <li class="border-b border-black">
                        <span class="uppercase font-semibold">sawit</span>
                        <ul>
                            <li>
                                <span class="text-red-700 text-xl">•</span>
                                <a href="">CBI</a>
                            </li>
                            <li>
                                <span class="text-red-700 text-xl">•</span>
                                <a href="">ANJ</a>
                            </li>
                        </ul>
                    </li>
                    <div class="font-semibold">
                        <span>Follow us</span>
                        <div class="flex space-x-2 mt-2">
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                fill="#000000" height="25px" width="25px" version="1.1" id="Layer_1"
                                viewBox="-143 145 512 512" xml:space="preserve">
                                <path
                                    d="M113,145c-141.4,0-256,114.6-256,256s114.6,256,256,256s256-114.6,256-256S254.4,145,113,145z M215.2,361.2  c0.1,2.2,0.1,4.5,0.1,6.8c0,69.5-52.9,149.7-149.7,149.7c-29.7,0-57.4-8.7-80.6-23.6c4.1,0.5,8.3,0.7,12.6,0.7  c24.6,0,47.3-8.4,65.3-22.5c-23-0.4-42.5-15.6-49.1-36.5c3.2,0.6,6.5,0.9,9.9,0.9c4.8,0,9.5-0.6,13.9-1.9  C13.5,430-4.6,408.7-4.6,383.2v-0.6c7.1,3.9,15.2,6.3,23.8,6.6c-14.1-9.4-23.4-25.6-23.4-43.8c0-9.6,2.6-18.7,7.1-26.5  c26,31.9,64.7,52.8,108.4,55c-0.9-3.8-1.4-7.8-1.4-12c0-29,23.6-52.6,52.6-52.6c15.1,0,28.8,6.4,38.4,16.6  c12-2.4,23.2-6.7,33.4-12.8c-3.9,12.3-12.3,22.6-23.1,29.1c10.6-1.3,20.8-4.1,30.2-8.3C234.4,344.5,225.5,353.7,215.2,361.2z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="#000000" width="25px" height="25px"
                                viewBox="0 0 32 32" version="1.1">
                                <title>facebook</title>
                                <path
                                    d="M30.996 16.091c-0.001-8.281-6.714-14.994-14.996-14.994s-14.996 6.714-14.996 14.996c0 7.455 5.44 13.639 12.566 14.8l0.086 0.012v-10.478h-3.808v-4.336h3.808v-3.302c-0.019-0.167-0.029-0.361-0.029-0.557 0-2.923 2.37-5.293 5.293-5.293 0.141 0 0.281 0.006 0.42 0.016l-0.018-0.001c1.199 0.017 2.359 0.123 3.491 0.312l-0.134-0.019v3.69h-1.892c-0.086-0.012-0.185-0.019-0.285-0.019-1.197 0-2.168 0.97-2.168 2.168 0 0.068 0.003 0.135 0.009 0.202l-0.001-0.009v2.812h4.159l-0.665 4.336h-3.494v10.478c7.213-1.174 12.653-7.359 12.654-14.814v-0z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                fill="#000000" height="25px" width="25px" version="1.1" id="Layer_1"
                                viewBox="-143 145 512 512" xml:space="preserve">
                                <g>
                                    <path
                                        d="M113,446c24.8,0,45.1-20.2,45.1-45.1c0-9.8-3.2-18.9-8.5-26.3c-8.2-11.3-21.5-18.8-36.5-18.8s-28.3,7.4-36.5,18.8   c-5.3,7.4-8.5,16.5-8.5,26.3C68,425.8,88.2,446,113,446z" />
                                    <polygon
                                        points="211.4,345.9 211.4,308.1 211.4,302.5 205.8,302.5 168,302.6 168.2,346  " />
                                    <path
                                        d="M183,401c0,38.6-31.4,70-70,70c-38.6,0-70-31.4-70-70c0-9.3,1.9-18.2,5.2-26.3H10v104.8C10,493,21,504,34.5,504h157   c13.5,0,24.5-11,24.5-24.5V374.7h-38.2C181.2,382.8,183,391.7,183,401z" />
                                    <path
                                        d="M113,145c-141.4,0-256,114.6-256,256s114.6,256,256,256s256-114.6,256-256S254.4,145,113,145z M241,374.7v104.8   c0,27.3-22.2,49.5-49.5,49.5h-157C7.2,529-15,506.8-15,479.5V374.7v-52.3c0-27.3,22.2-49.5,49.5-49.5h157   c27.3,0,49.5,22.2,49.5,49.5V374.7z" />
                                </g>
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="#000000" width="25px" height="25px"
                                viewBox="0 0 32 32">

                                <title />

                                <g id="Linkedln">

                                    <path
                                        d="M26.49,30H5.5A3.35,3.35,0,0,1,3,29a3.35,3.35,0,0,1-1-2.48V5.5A3.35,3.35,0,0,1,3,3,3.35,3.35,0,0,1,5.5,2h21A3.35,3.35,0,0,1,29,3,3.35,3.35,0,0,1,30,5.5v21A3.52,3.52,0,0,1,26.49,30ZM9.11,11.39a2.22,2.22,0,0,0,1.6-.58,1.83,1.83,0,0,0,.6-1.38A2.09,2.09,0,0,0,10.68,8a2.14,2.14,0,0,0-1.51-.55A2.3,2.3,0,0,0,7.57,8,1.87,1.87,0,0,0,7,9.43a1.88,1.88,0,0,0,.57,1.38A2.1,2.1,0,0,0,9.11,11.39ZM11,13H7.19V24.54H11Zm13.85,4.94a5.49,5.49,0,0,0-1.24-4,4.22,4.22,0,0,0-3.15-1.27,3.44,3.44,0,0,0-2.34.66A6,6,0,0,0,17,14.64V13H13.19V24.54H17V17.59a.83.83,0,0,1,.1-.43,2.73,2.73,0,0,1,.7-1,1.81,1.81,0,0,1,1.28-.44,1.59,1.59,0,0,1,1.49.75,3.68,3.68,0,0,1,.44,1.9v6.15h3.85ZM17,14.7a.05.05,0,0,1,.06-.06v.06Z" />

                                </g>

                            </svg>
                        </div>
                    </div>
                </ul>
            </div>
        </div>
    </div>
</nav>