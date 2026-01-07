<div class="bg-gray-100 mt-[200px] font-open">
    <div class="max-w-screen-lg mx-auto py-8 px-4 text-center text-black space-y-4">
        @php $locale = app()->getLocale(); @endphp
        @if ($locale === 'id')
            <div class="space-y-3 text-sm md:text-base leading-relaxed">
                <p>
                    Pasopati Project dirancang sebagai sebuah situs yang menampilkan informasi, data, dan analisis
                    isu-isu kehutanan, persawitan, dan pertambangan. Situs ini fokus menyampaikan suara kritis pada
                    tema-tema tersebut, termasuk mengenai pelakunya dan kebijakan-kebijakan terkait.
                </p>
                <p>
                    Pasopati Project didedikasikan untuk mencapai salah satu tujuan Auriga, yakni mengeliminir
                    aksi-aksi destruktif terhadap sumberdaya alam.
                </p>
                <p>
                    Situs ini dikelola oleh Auriga. Namun demikian ekspose-ekspose tertentu dalam situs ini dilakukan
                    bersama jejaring.
                </p>
            </div>
        @else
            <div class="space-y-3 text-sm md:text-base leading-relaxed">
                <p>
                    Pasopati Project is designed as a platform to present information, data, and analysis regarding
                    issues related to forestry, oil palm, and mining in Indonesia. This website focuses on delivering
                    critical perspectives and insights on these issues, including related actors and government
                    policies.
                </p>
                <p>
                    The Pasopati Project website is intended to fulfill one of Yayasan Auriga’s goals: to eliminate
                    destructive actions related to natural resource exploitation in Indonesia.
                </p>
                <p>
                    The site is managed by Auriga, with particular analyses conducted in conjunction with civil society
                    coalitions.
                </p>
            </div>
        @endif
    </div>
</div>

<footer class="bg-black text-white py-10 font-sans">
    <div class="max-w-screen-lg mx-auto flex flex-col md:flex-row items-center justify-between gap-6 px-6">
        <p class="text-center md:text-left text-sm md:text-base">
            © AURIGA NUSANTARA. ALL RIGHTS RESERVED.
        </p>

        <div class="flex flex-col md:flex-row items-center gap-3">
            <p class="uppercase font-semibold tracking-wide">Follow :</p>
            <div class="flex gap-4">
                <!-- Twitter/X -->
                <a href="#" aria-label="Twitter" class="hover:opacity-75 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" height="22" width="22" viewBox="0 0 640 640" fill="white">
                        <path
                            d="M160 96C124.7 96 96 124.7 96 160V480C96 515.3 124.7 544 160 544H480C515.3 544 544 515.3 544 480V160C544 124.7 515.3 96 480 96H160zM447.3 263.3C447.3 350 381.3 449.9 260.7 449.9C223.5 449.9 189 439.1 160 420.5C165.3 421.1 170.4 421.3 175.8 421.3C206.5 421.3 234.7 410.9 257.2 393.3C228.4 392.7 204.2 373.8 195.9 347.8C206 349.3 215.1 349.3 225.5 346.6C195.5 340.5 173 314.1 173 282.2L173 281.4C181.7 286.3 191.9 289.3 202.6 289.7C193.6 283.7 186.2 275.6 181.1 266.1C176 256.6 173.3 245.9 173.4 235.1C173.4 222.9 176.6 211.7 182.3 202C214.6 241.8 263.1 267.8 317.5 270.6C308.2 226.1 341.5 190 381.5 190C400.4 190 417.4 197.9 429.4 210.7C444.2 207.9 458.4 202.4 471 194.9C466.1 210.1 455.8 222.9 442.2 231C455.4 229.6 468.2 225.9 480 220.8C471.1 233.9 459.9 245.5 447.1 254.8C447.3 257.6 447.3 260.5 447.3 263.3z" />
                    </svg>
                </a>

                <!-- Facebook -->
                <a href="#" aria-label="Facebook" class="hover:opacity-75 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" height="22" width="22" viewBox="0 0 640 640" fill="white">
                        <path
                            d="M160 96C124.7 96 96 124.7 96 160V480C96 515.3 124.7 544 160 544H258.2V398.2H205.4V320H258.2V286.3C258.2 199.2 297.6 158.8 383.2 158.8C399.4 158.8 427.4 162 438.9 165.2V236C432.9 235.4 422.4 235 409.3 235C367.3 235 351.1 250.9 351.1 292.2V320H434.7L420.3 398.2H351V544H480C515.3 544 544 515.3 544 480V160C544 124.7 515.3 96 480 96H160z" />
                    </svg>
                </a>

                <!-- Instagram -->
                <a href="">
                    <svg xmlns="http://www.w3.org/2000/svg" height="25px" width="25px" viewBox="0 0 640 640">
                        <!--!Font Awesome Free v7.0.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                        <path fill="#ffffff"
                            d="M290.4 275.7C274 286 264.5 304.5 265.5 323.8C266.6 343.2 278.2 360.4 295.6 368.9C313.1 377.3 333.8 375.5 349.6 364.3C366 354 375.5 335.5 374.5 316.2C373.4 296.8 361.8 279.6 344.4 271.1C326.9 262.7 306.2 264.5 290.4 275.7zM432.7 207.3C427.5 202.1 421.2 198 414.3 195.3C396.2 188.2 356.7 188.5 331.2 188.8C327.1 188.8 323.3 188.9 320 188.9C316.7 188.9 312.8 188.9 308.6 188.8C283.1 188.5 243.8 188.1 225.7 195.3C218.8 198 212.6 202.1 207.3 207.3C202 212.5 198 218.8 195.3 225.7C188.2 243.8 188.6 283.4 188.8 308.9C188.8 313 188.9 316.8 188.9 320C188.9 323.2 188.9 327 188.8 331.1C188.6 356.6 188.2 396.2 195.3 414.3C198 421.2 202.1 427.4 207.3 432.7C212.5 438 218.8 442 225.7 444.7C243.8 451.8 283.3 451.5 308.8 451.2C312.9 451.2 316.7 451.1 320 451.1C323.3 451.1 327.2 451.1 331.4 451.2C356.9 451.5 396.2 451.9 414.3 444.7C421.2 442 427.4 437.9 432.7 432.7C438 427.5 442 421.2 444.7 414.3C451.9 396.3 451.5 356.9 451.2 331.3C451.2 327.1 451.1 323.2 451.1 319.9C451.1 316.6 451.1 312.8 451.2 308.5C451.5 283 451.9 243.6 444.7 225.5C442 218.6 437.9 212.4 432.7 207.1L432.7 207.3zM365.6 251.8C383.7 263.9 396.2 282.7 400.5 304C404.8 325.3 400.3 347.5 388.2 365.6C382.2 374.6 374.5 382.2 365.6 388.2C356.7 394.2 346.6 398.3 336 400.4C314.7 404.6 292.5 400.2 274.4 388.1C256.3 376 243.8 357.2 239.5 335.9C235.2 314.6 239.7 292.4 251.7 274.3C263.7 256.2 282.6 243.7 303.9 239.4C325.2 235.1 347.4 239.6 365.5 251.6L365.6 251.6zM394.8 250.5C391.7 248.4 389.2 245.4 387.7 241.9C386.2 238.4 385.9 234.6 386.6 230.8C387.3 227 389.2 223.7 391.8 221C394.4 218.3 397.9 216.5 401.6 215.8C405.3 215.1 409.2 215.4 412.7 216.9C416.2 218.4 419.2 220.8 421.3 223.9C423.4 227 424.5 230.7 424.5 234.5C424.5 237 424 239.5 423.1 241.8C422.2 244.1 420.7 246.2 419 248C417.3 249.8 415.1 251.2 412.8 252.2C410.5 253.2 408 253.7 405.5 253.7C401.7 253.7 398 252.6 394.9 250.5L394.8 250.5zM544 160C544 124.7 515.3 96 480 96L160 96C124.7 96 96 124.7 96 160L96 480C96 515.3 124.7 544 160 544L480 544C515.3 544 544 515.3 544 480L544 160zM453 453C434.3 471.7 411.6 477.6 386 478.9C359.6 480.4 280.4 480.4 254 478.9C228.4 477.6 205.7 471.7 187 453C168.3 434.3 162.4 411.6 161.2 386C159.7 359.6 159.7 280.4 161.2 254C162.5 228.4 168.3 205.7 187 187C205.7 168.3 228.5 162.4 254 161.2C280.4 159.7 359.6 159.7 386 161.2C411.6 162.5 434.3 168.3 453 187C471.7 205.7 477.6 228.4 478.8 254C480.3 280.3 480.3 359.4 478.8 385.9C477.5 411.5 471.7 434.2 453 452.9L453 453z" />
                    </svg>
                </a>

                <!-- LinkedIn -->
                <a href="#" aria-label="LinkedIn" class="hover:opacity-75 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" height="22" width="22" viewBox="0 0 640 640" fill="white">
                        <path
                            d="M480 96H160C124.7 96 96 124.7 96 160V480C96 515.3 124.7 544 160 544H480C515.3 544 544 515.3 544 480V160C544 124.7 515.3 96 480 96zM231.4 480H165V266.2H231.5V480H231.4zM198.2 160C219.5 160 236.7 177.2 236.7 198.5C236.7 219.8 219.5 237 198.2 237C176.9 237 159.7 219.8 159.7 198.5C159.7 177.2 176.9 160 198.2 160zM480.3 480H413.9V376C413.9 351.2 413.4 319.3 379.4 319.3C344.8 319.3 339.5 346.3 339.5 374.2V480H273.1V266.2H336.8V295.4H337.7C346.6 278.6 368.3 260.9 400.6 260.9C467.8 260.9 480.3 305.2 480.3 362.8V480z" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</footer>
