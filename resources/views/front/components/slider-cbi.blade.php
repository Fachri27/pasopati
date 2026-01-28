<div class="max-w-[1400px] mx-auto p-6 mt-20" x-data="slideViewer()">

    <!-- NAV -->
    <div class="flex justify-end gap-3 mb-4">
        <button @click="prev()" :disabled="current === 0"
            class="bg-[#d71920] disabled:opacity-40 hover:bg-red-700 text-white px-4 py-2 rounded font-semibold">
            PREV
        </button>

        <button @click="next()" :disabled="current === slides.length - 1"
            class="bg-[#d71920] disabled:opacity-40 hover:bg-red-700 text-white px-4 py-2 rounded font-semibold">
            NEXT
        </button>
    </div>

    <!-- SLIDE -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- LEFT -->
        <div class="lg:col-span-2 bg-white rounded shadow">
            <div class="p-4 border-b">
                <h2 class="text-lg font-semibold" x-text="slides[current].left.title"></h2>

                <p class="text-sm text-gray-500" x-text="slides[current].left.subtitle"></p>
            </div>

            <!-- MEDIA -->
            <div class="w-full h-[550px] bg-gray-50 flex items-center justify-center">

                <!-- IFRAME -->
                <template x-if="slides[current].media.type === 'iframe'">
                    <iframe :src="slides[current].media.src" class="w-full h-full border-0" loading="lazy">
                    </iframe>
                </template>

                <!-- IMAGE -->
                <template x-if="slides[current].media.type === 'image'">
                    <img :src="slides[current].media.src" class="max-w-full max-h-full object-contain" alt="">
                </template>

            </div>
        </div>

        <!-- RIGHT -->
        <div class="bg-white rounded shadow flex flex-col h-[650px]">

            <!-- HEADER (TETAP) -->
            <div class="p-6 border-b shrink-0">
                <p class="text-sm text-green-600 mb-1" x-text="`${current + 1} dari ${slides.length}`"></p>

                <h3 class="text-xl font-bold" x-text="slides[current].narrative.title"></h3>
            </div>

            <!-- BODY (SCROLL DI SINI) -->
            <div class="p-6 overflow-y-auto flex-1 text-sm text-gray-700 leading-relaxed space-y-6">

                <template x-for="(section, i) in slides[current].narrative.sections" :key="i">

                    <div class="space-y-3">
                        <template x-for="(p, j) in section.paragraphs" :key="j">
                            <p x-text="p"></p>
                        </template>
                    </div>

                </template>

            </div>

        </div>


    </div>
</div>
<script>
    function slideViewer() {
        return {
            current: 0,

            slides: [
                {
                    left: {
                        title: 'Struktur CBI Group',
                        subtitle: 'Anak Usaha dan Pemegang Saham'
                    },

                    media: {
                        type: 'iframe',
                        src: 'https://embed.kumu.io/32d1e6676238ae20daa0eefdd4002c4a#untitled-map/shareholder-id'
                    },

                    narrative: {
                        title: 'Sekilas tentang CBI Group',
                        sections: [
                            {
                                paragraphs: [
                                    'CBI Group didirikan pada 1982 oleh seorang pengusaha kaya, Haji Abdul Rasyid Akhmad Saleh (AR). Saat itu, grup tersebut menggunakan nama lain, Grup Tanjung Lingga. Selama beberapa dekade di sektor sumber daya alam di Kalimantan Tengah, CBI Group telah terlibat dalam kasus-kasus deforestasi, pelanggaran hak asasi manusia, dan konflik lahan dengan masyarakat lokal. Dengan setidaknya tujuh anggota keluarga Abdul Rasyid pernah menjabat sebagai gubernur, bupati, dan anggota legislatif, pemilik mayoritas grup juga memiliki pengaruh politik yang signifikan.'
                                ]
                            }
                        ]
                    }
                },
                {
                    left: {
                        title: 'Konsensi Anak Perusahaan CBI Group',
                        subtitle: 'Konsesi yang dikeluarkan pemerintah berdasarkan hektar, waktu diterbitkan izin, dan tujuan'
                    },

                    media: {
                        type: 'iframe',
                        src: 'https://flo.uri.sh/visualisation/4658462/embed'
                    },

                    narrative: {
                        title: 'Landbank CBI Group',
                        sections: [
                            {
                                paragraphs: [
                                    'CBI Group tercatat di Bursa Efek Indonesia (BEI) melalui salah satu anak perusahaannya, yaitu PT Sawit Sumbermas Sarana (SSMS). Namun, keluarga AR adalah pemegang saham terbesar grup. Pada 2017, landbank Grup CBI mencapai 135.471 hektare, semuanya berada di Kalimantan Tengah.'
                                ]
                            },
                            {
                                paragraphs: [
                                    'Pulau Kalimantan adalah rumah bagi hampir 40 juta hektar hutan hujan, menjadikannya salah satu penyerap karbon terbesar di dunia. Meskipun deforestasi di Indonesia secara keseluruhan telah menurun dalam beberapa tahun terakhir, deforestasi di Kalimantan tetap lebih tinggi daripada di hampir semua wilayah lain di Indonesia, dan saat ini meningkat di Kalimantan Timur. Industri sawit merupakan salah satu penyebab utama deforestasi di Indonesia.'
                                ]
                            }
                        ]
                    }
                },
                {
                    left: {
                        title: 'Penutupan jalan suplai PT SML',
                        subtitle: 'Penduduk desa membuat pos penjagaan dan memasang portal di jalan PT. SML yang terus menggusur hutan adat Laman Kinipan dan mengambil kayu termasuk kayu ulin'
                    },

                    media: {
                        type: 'image',
                        src: '/img/gambar3.jpg'
                    },

                    narrative: {
                        title: 'Deforestasi di Tanah Adat',
                        sections: [
                            {
                                paragraphs: [
                                    'Rekaman drone yang diulas oleh Lucida dan wawancara dengan masyarakat adat Laman Kinipan menunjukkan bahwa Grup CBI terhubung dengan deforestasi lahan yang telah dihuni oleh masyarakat Kinipan sejak tahun 1870 melalui anak perusahaannya, PT Sawit Mandiri Lestari (SML). Masyarakat Kinipan melanjutkan tradisi budaya dan adat istiadat yang telah turun temurun, seperti bertani dan berburu. Bagi mereka, hutan merupakan sumber kayu ulin untuk bahan bangunan, madu hutan di pohon tapan, dan obat-obatan tradisional.'
                                ]
                            },
                            {
                                paragraphs: [
                                    'Sejak 2005, warga Desa Kinipan telah menolak upaya PT SML untuk mengembangkan lahan mereka dengan mengirim surat kepada Bupati Lamandau yang menyatakan penolakan mereka terhadap perkebunan sawit. Terlepas dari protes warga, Bupati telah mengeluarkan beberapa izin dan persetujuan yang diperlukan kepada PT SML. '
                                ]
                            },
                            {
                                paragraphs: [
                                    'Setelah kegiatan pembukaan lahan dimulai pada Januari 2018, penduduk desa mengundang manajemen PT SML untuk bertemu untuk mencoba menyelesaikan sengketa kepemilikan lahan mereka; namun, dalam wawancara, penduduk desa menyatakan bahwa PT SML tidak hadir dalam pertemuan tersebut. Baru-baru ini, pada pertengahan 2020, warga membuat pos jaga dan memasang blokade di jalan yang digunakan PT SML untuk membuka hutan adat Laman Kinipan. Aksi ini berujung pada penangkapan lima warga Desa Kinipan dan Batu Tambun dengan tuduhan merusak alat berat dan gergaji mesin yang digunakan PT SML.'
                                ]
                            },
                            {
                                paragraphs: [
                                    'Bentrokan masyarakat Kinipan dengan PT SML memuncak pada Agustus lalu, ketika tokoh adat mereka, Effendi Buhing, ditangkap aparat Polda Kalteng dengan tuduhan memerintahkan pencurian dan tindak kekerasan. Setelah penangkapannya, anggota masyarakat membuat petisi change.org dan mengorganisir konferensi pers menuntut pembebasan Buhing dan lima warga Kinipan lainnya. Selain itu, Panglima Jilah, pemimpin pasukan pertahanan masyarakat adat yang dikenal dengan “Pasukan Merah”, memberikan ultimatum melalui video pendek dengan mengancam akan mengerahkan pasukan ke seluruh Kalimantan Tengah jika Effendi Buhing tidak dibebaskan. Effendi Buhing dibebaskan setelah sekitar 24 jam.Warga juga menuduh PT SML mencemari sungai yang menjadi sumber air minum masyarakat, yang mengalir dari wilayah yang saat ini dikuasai PT SML. Warga desa menyatakan banyak ikan yang mati karena keracunan. Namun, pengujian lebih lanjut diperlukan untuk mengkonfirmasi dampak lingkungan dan sosial.'
                                ]
                            },
                        ]
                    }
                },
                {
                    left: {
                        title: 'Waktu Aktifitas',
                        subtitle: 'Lini masa Konflik CBI Group dan Masyarakat Adat Kinipan'
                    },

                    media: {
                        type: 'iframe',
                        src: 'https://flo.uri.sh/visualisation/4661202/embed'
                    },

                    narrative: {
                        title: 'Potensi Pelanggaran Hukum dan FPIC',
                        sections: [
                            {
                                paragraphs: [
                                    'Hak Guna Usaha (HGU) PT SML tampaknya tidak memenuhi persyaratan hukum untuk diterbitkan. Sesuai Undang-Undang No. 39 Tahun 2014 tentang Perkebunan, sengketa status pengusahaan PT SML seharusnya diselesaikan sebelum diterbitkan, namun sengketa mengenai hutan adat masyarakat Kinipan dan batas administrasi antar desa sedang berlangsung saat izin diterbitkan. Demikian pula dalam peraturan Permentan Nomor 98/Permentan/OT.140/9/2013 tentang Pedoman Perizinan Usaha Perkebunan menyatakan bahwa jika ada masyarakat adat di atas tanah yang akan disertifikasi, perusahaan harus melibatkan masyarakat dalam proses pengukuran dan pemetaan. Namun, pengukuran dan pemetaan lahan yang termasuk dalam HGU PT SML tidak pernah melibatkan Masyarakat Adat Kinipan. Selain berpotensi melanggar hukum Indonesia, kegagalan PT SML untuk menghentikan perluasan lahan sampai sengketa terkait diselesaikan dan melibatkan masyarakat Kinipan dalam proses pengukuran dan pemetaan, melanggar kebijakan banyak perusahaan dan pemodal yang mensyaratkan Persetujuan Bebas, Didahulukan, dan Diinformasikan (FPIC) dari masyarakat yang terkena dampak sebelum pembangunan.'
                                ]
                            }
                        ]
                    }
                },
                {
                    left: {
                        title: 'Spesies yang terancam punah di konsesi PT SML',
                        subtitle: 'Konsesi PT SML mencakup 16.857 hektar hutan sekunder yang teridentifikasi sebagai habitat orangutan Kalimantan, macan dahan, flora terancam punah dan puluhan spesies langka lainnya.'
                    },

                    media: {
                        type: 'image',
                        src: '/img/gambar5.jpg'
                    },

                    narrative: {
                        title: 'Terancamnya Keanekaragaman Hayati dan Konservasi',
                        sections: [
                            {
                                paragraphs: [
                                    'Perkebunan sawit PT SML mengancam spesies yang terancam punah dan ekosistem yang penting. Menurut assessment yang dilakukan oleh perusahaan, perkebunan PT SML memiliki area Nilai Konservasi Tinggi (HCV) seluas 4.832,83 hektar, yang merupakan hampir 18 persen dari total luas perkebunan (26.995,46 hektar). Kawasan tersebut berisi populasi spesies yang terancam punah, ekosistem yang penting untuk penyediaan air dan pencegahan banjir bagi masyarakat di hilir, serta kawasan yang digunakan oleh masyarakat lokal untuk persediaan makanan dan bangunan. Pada 2014, PT Sonokeling Akreditas Nusantara melakukan penilaian NKT atas nama PT SML, sebagai prasyarat untuk penanaman baru oleh anggota RSPO. Namun, penyelidikan lapangan oleh Environmental Investigation Agency (EIA) dan Jaringan Pemantau Independen Kehutanan (JPIK) menemukan beberapa indikasi bahwa perusahaan melakukan penilaian dengan tidak benar. Salah satu indikasinya adalah bahwa angka PT SML untuk kawasan NKT tampaknya hanya mencakup kawasan-kawasan seperti pegunungan dan bantaran sungai, di mana hukum Indonesia memang melarang penanaman sawit di kawasan tersebut karena kepentingan ekologisnya. Selain itu, laporan penilaian mencatat keberadaan trenggiling sunda (manis javanica) di dalam konsesi, tetapi PT SML tidak mengidentifikasinya dengan benar sebagai spesies yang terancam punah. Kawasan NKT yang diidentifikasi dalam penilaian PT SML tampaknya tidak cukup terhubung satu sama lain dengan Kawasan Konservasi Belantikan dan Suaka Margasatwa Lamandau di dekatnya, untuk menopang populasi orangutan borneo, macan dahan, flora yang terancam punah, dan puluhan spesies lainnya. '
                                ]
                            }
                        ]
                    }
                },
                {
                    left: {
                        title: 'Komplain terhadap RSPO',
                        subtitle: 'Kekhawatiran termasuk kurangnya konsultasi masyarakat, Analisis Mengenai Dampak Lingkungan yang cacat, tidak ada proses Persetujuan Bebas, Didahulukan, dan Diinformasikan, dan izin yang tidak memadai'
                    },

                    media: {
                        type: 'image',
                        src: '/img/gambar6.png'
                    },

                    narrative: {
                        title: 'Pengaduan RSPO dan Konsekuensi Keuangan',
                        sections: [
                            {
                                paragraphs: [
                                    'Ketidakberesan dalam penilaian NKT PT SML dan kegagalannya untuk melakukan FPIC dalam berurusan dengan Masyarakat Adat Kinipan mendorong EIA dan JPIK untuk mengajukan pengaduan ke RSPO pada Mei 2015. Pemilik PT SML saat itu yaitu PT SSMS – satu-satunya anak perusahaan CBI Group yang diperdagangkan secara publik – ditangguhkan oleh tiga pembeli utamanya, dan labanya merosot sekitar USD 30 juta dalam periode dua kuartal. PT SSMS kehilangan 81 persen basis pelanggannya pada 2014-2015. Pada Desember 2015, PT SSMS menjual PT SML ke PT Metro Jaya Lestari (MJL). Karena MJL bukan anggota RSPO, maka RSPO menutup pengaduan setelah penjualan selesai.'
                                ]
                            }
                        ]
                    }
                },
                {
                    left: {
                        title: 'Struktur Kepemilikan PT SML',
                        subtitle: 'Memahami hubungan keuangan dan keluarga antara PT SML dan Grup CBI'
                    },

                    media: {
                        type: 'iframe',
                        src: 'https://embed.kumu.io/32d1e6676238ae20daa0eefdd4002c4a#untitled-map/shareholder-id'
                    },

                    narrative: {
                        title: 'Mengurai Struktur Kepemilikan PT SML',
                        sections: [
                            {
                                paragraphs: [
                                    'Meskipun tidak lagi menjadi anak perusahaan resmi dari grup CBI, PT SML mempertahankan ikatan yang compleks dan tidak langsung dengan grup tersebut, sebuah fakta yang telah membantu pemilik dan pemodal Grup CBI untuk menghindari pengawasan.'
                                ]
                            },
                            {
                                paragraphs: [
                                    'Per April 2020, pemegang saham PT SML hanya PT MJL (99,9%) dan PT Agro Jaya Gemilang (AJG) (0,1%) (sesuai akta notaris 21 April 2020). Baik PT MJL maupun PT AJG dimiliki oleh Afrian Fanani (51%) dan Ahmadin (49%). Fanani adalah anak tiri AR, dan Ahmadin adalah saudara ipar AR. Selain menjadi pemegang saham PT MJL dan PT AJG, Fanani adalah salah satu dari tiga pemegang saham di PT Surya Borneo Energy, dan Ahmadin adalah komisaris PT Pelayaran Lingga Marintama – keduanya merupakan anak perusahaan dari grup CBI. Artinya, meskipun PT SML tidak lagi memiliki hubungan formal dengan PT CBI atau CBI Group, PT SML dimiliki oleh keluarga yang sama. Ikatan yang kuat antara pemilik PT SML dan Grup CBI menimbulkan pertanyaan seputar independensi pengambilan keputusan operasional.'
                                ]
                            }
                        ]
                    }
                },
                {
                    left: {
                        title: 'Deforestasi Grup CBI dari waktu ke waktu',
                        subtitle: 'Deforestasi Perusahaan di CBI Group 2003-2019'
                    },

                    media: {
                        type: 'iframe',
                        src: 'https://flo.uri.sh/visualisation/4668898/embed'
                    },

                    narrative: {
                        title: 'Deforestasi di Perusahaan Grup CBI',
                        sections: [
                            {
                                paragraphs: [
                                    'Seperti yang ditunjukkan grafik, PT SML bukan satu-satunya perusahaan Grup CBI yang terlibat dalam deforestasi.'
                                ]
                            },
                            {
                                paragraphs: [
                                    'PT SSMS, misalnya, terus melanggar kebijakan NDPE pembelinya sejak menjual PT SML pada 2015. Akibatnya, PT SSMS ditangguhkan oleh Unilever pada 2017 dan oleh IFFCO pada 2018. IFFCO menyumbang 20 persen dari pendapatan PT SSMS. Penangguhan pembelian yang dilakukan oleh Wilmar, Golden Agri Resources (GAR), dan Apical pada 2015 dikombinasikan dengan penangguhan berikutnya, diduga telah berkontribusi pada rendahnya kinerja harga saham PT SSMS dibandingkan dengan indeks acuan yang relevan.'
                                ]
                            },
                            {
                                paragraphs: [
                                    'Deforestasi yang dilakukan CBI Group dimulai jauh sebelum ekspansi ke bisnis sawit. Pada 1990-an dan awal 2000-an, perusahaan terlibat dalam penebangan, sebagian besar ilegal. Pendiri grup, AR, dilaporkan menjadi kekuatan pendorong di balik keuntungan dari kayu berharga yang terletak di dalam Taman Nasional Tanjung Puting, termasuk Ramin dan Ironwood.'
                                ]
                            }   
                        ]
                    }
                },
                {
                    left: {
                        title: 'Relasi Anggota Keluarga AR',
                        subtitle: 'PT SML telah mengaburkan batas antara hubungan keluarga, operasi bisnis, dan pengawasan perusahaan'
                    },

                    media: {
                        type: 'iframe',
                        src: 'https://embed.kumu.io/7faa8142680269f9fb3e37c9c8d306cb#untitled-map/family-id '
                    },

                    narrative: {
                        title: 'Relasi dalam Grup CBI dan antara Grup CBI dan keluarga AR',
                        sections: [
                            {
                                paragraphs: [
                                    'CBI Group secara efektif dikendalikan oleh pendiri grup, AR, dan keluarganya. Setelah menjadi anggota Majelis Permusyawaratan Rakyat (MPR) perwakilan Kalimantan Tengah pada 2002, keponakan AR, Sugianto Sabran, Gubernur Kalimantan Tengah saat ini, menjadi Direktur Utama PT SSMS, sekaligus pemegang saham minoritas.'
                                ]
                            },
                            {
                                paragraphs: [
                                    'Kepemilikan perusahaan telah beralih ke anak AR, Jemmy Adriyanor, Jery Borneo Putra, Ernis Desidistrina, dan Monica Putri. Mereka memiliki PT Mandiri Indah Lestari dan PT Prima Sawit Borneo, pemegang saham mayoritas perusahaan induk grup, PT CBI. AR tetap menjadi Dewan Komisaris PT CBI, PT Citra Borneo Utama, dan PT Jery Borneo Putra.'
                                ]
                            },
                            {
                                paragraphs: [
                                    'Banyak anggota keluarga lainnya menjabat sebagai dewan direksi dan komisaris untuk perusahaan CBI dalam Grup CBI, dan beberapa anggota keluarga memegang posisi manajemen di beberapa perusahaan anggota. Selain itu, beberapa perusahaan anggota dihubungkan melalui hubungan kepemilikan saham silang, di mana perusahaan A memegang saham di perusahaan B, yang pada gilirannya memegang saham di perusahaan A.'
                                ]
                            },
                            {
                                paragraphs: [
                                    'Keluarga juga memiliki koneksi dan pengaruh politik yang signifikan. Bahkan, empat anggota keluarga saat ini memegang jabatan pemerintahan. Seperti disebutkan di atas, keponakan AR, Sugianto Sabran, saat ini menjabat sebagai Gubernur Kalimantan Tengah. Keponakan AR lainnya, Hendra Lesmana, saat ini menjabat sebagai Bupati Lamandau periode 2018-2023. Nurhidayah, adik ipar AR, menjabat Bupati Kotawaringin Barat sejak 2017. Terakhir, Muhammad Ruslan Ahmad Saleh, kakak AR, menjabat sebagai Ketua DPD Golkar Kalteng periode 2016-2019 dan terpilih kembali Maret lalu untuk periode 2020-2025.'
                                ]
                            },
                        ]
                    }
                },
                {
                    left: {
                        title: 'Pengaruh kepemimpinan dan Dewan Direksi CBI Group',
                        subtitle: 'Direktur dan komisaris CBI Group mengaburkan batasan di seluruh sektor minyak sawit Indonesia'
                    },

                    media: {
                        type: 'iframe',
                        src: 'https://embed.kumu.io/e26296ec17430911a4e54afc5eb110f7#untitled-map/bod-and-boc-id'
                    },

                    narrative: {
                        title: 'Keterkaitan antara AR dengan Grup CBI',
                        sections: [
                            {
                                paragraphs: [
                                    'Hubungan antara perusahaan anggota CBI Group dan hubungan grup dengan AR dikaburkan melalui struktur kepemilikan yang rumit, yang menimbulkan kekhawatiran seputar konflik kepentingan. Mirip dengan hubungan antara CBI Group dan PT SML, pengaturan ini memungkinkan pemilik dan pemodal grup untuk menghindari pengawasan. Jadi, PT SML telah mengaburkan batas antara hubungan keluarga, operasi bisnis, dan pengawasan perusahaan.'
                                ]
                            }
                        ]
                    }
                },
                {
                    left: {
                        title: 'Pemodal PT SSMS',
                        subtitle: 'Pemodal yang meminjamkan dan memiliki saham di PT SSMS'
                    },

                    media: {
                        type: 'iframe',
                        src: 'https://flo.uri.sh/visualisation/4679688/embed'
                    },

                    narrative: {
                        title: 'CBI Group dan Pemodal',
                        sections: [
                            {
                                paragraphs: [
                                    'CBI Group tercatat di Bursa Efek Indonesia melalui PT SSMS. Sejak 2018, PT CBI – perusahaan induk dari CBI Group – menjadi pemegang saham terbesar PT SSMS sebesar 53,75 persen. Pemegang saham lainnya termasuk beberapa bank terkemuka, manajer aset, dan perusahaan asuransi, termasuk Blackrock, State Street, Dimensional, NYLife, Avantis, dan Nuveen (lihat tabel).'
                                ]
                            },
                            {
                                paragraphs: [
                                    'Mengingat hubungan formal dan kekeluargaan yang luas antara PT SSMS dan grup CBI lainnya, pemodal PT SSMS terhubung tidak hanya ke PT SSMS itu sendiri, tetapi juga ke Grup CBI secara keseluruhan dan, dengan perluasan, ke AR, keluarganya, dan PT SML.'
                                ]
                            }
                        ]
                    }
                },
                {
                    left: {
                        title: 'Fokus yang berkembang pada risiko LST',
                        subtitle: 'Dampak ESG terhadap Investasi'
                    },

                    media: {
                        type: 'iframe',
                        src: 'https://flo.uri.sh/visualisation/4670769/embed'
                    },

                    narrative: {
                        title: 'Grup CBI take aways',
                        sections: [
                            {
                                paragraphs: [
                                    'Saat ini, terdapat kecenderungan dari para pemodal yang menyadari risiko keuangan dari penerapan standar Environment, Social, Governance yang buruk, seperti yang dilakukan CBI Group berikut ini:'
                                ]
                            }
                        ]
                    }
                },
            ],

            next() {
                if (this.current < this.slides.length - 1) this.current++
            },

            prev() {
                if (this.current > 0) this.current--
            }
        }
    }
</script>