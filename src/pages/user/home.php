<?php
$pageTitle = 'home';
include INCLUDES_PATH . "/user/layout/header.php";
?>

<div class="flex-1">

  <section
    class="relative w-full h-[22rem] md:h-[32rem] md:py-8 py-4 md:px-20 overflow-hidden block"
    x-data="carousel"
    x-init="init()">

    <!-- WRAPPER -->
    <div class="relative w-full h-full">
      <template x-for="(item, index) in hero" :key="index">
        <!-- SLIDE -->
        <div
          x-cloak
          class="absolute inset-0 transition-opacity duration-700"
          :class="current === index ? 'opacity-100' : 'opacity-0'">

          <div
            class="absolute inset-0 bg-cover bg-center flex items-center px-6 md:px-20"
            :style="`background-image: url('${uploadsUrl}${item.image_path}')`">

            <!-- overlay -->
            <div class="absolute inset-0 bg-black/40"></div>

            <!-- text -->
            <div class="absolute bottom-10 z-10 text-white max-w-xl space-y-2 md:space-y-4 font-poppins">
              <h1 class="text-2xl md:text-5xl font-bold" x-text="item.title"></h1>
              <p class="text-base md:text-lg" x-text="item.subtitle"></p>
              <p class="text-xs md:text-sm" x-text="item.TEXT"></p>
              <div class="flex gap-3">
                <template x-if="item.cta_primary_text">
                  <a :href="item.cta_primary_url"
                    class="px-3 py-1 md:px-5 md:py-3 w-fit  bg-gg-primary/90 rounded-lg shadow shadow-gg-primary/50 text-white">
                    <span x-text="item.cta_primary_text"></span>
                  </a>
                </template>
                <template x-if="item.cta_secondary_text">
                  <a :href="item.cta_secondary_url"
                    class="px-5 py-2 w-fit bg-white/90 rounded-lg shadow shadow-white/50 text-slate-900">
                    <span x-text="item.cta_secondary_text"></span>
                  </a>
                </template>
              </div>
            </div>

          </div>
        </div>


      </template>
    </div>

    <!-- dots -->
    <div class="absolute bottom-6 md:bottom-14 left-0 right-0 flex justify-center gap-2 z-10">
      <template x-for="(_, i) in hero" :key="i">
        <div
          class="h-3 rounded-full"
          @click="goTo(i)"
          :class="current === i ? 'bg-gg-primary w-8' : 'bg-white/60 w-3'"></div>
      </template>
    </div>

  </section>


  <section>
    <div class="h-[500px]">
      Lorem ipsum dolor sit amet consectetur, adipisicing elit. Ratione quam repudiandae hic illo. Molestiae, repudiandae! Eum ipsum aspernatur fugit ab sequi sint consectetur distinctio iure, reprehenderit error, enim suscipit soluta perferendis officia at. Necessitatibus ipsa quis cum. Earum consequuntur harum voluptas veniam a amet numquam quaerat quo ab commodi at autem perspiciatis, deserunt id officia corporis officiis sint doloremque? Sequi tempora dolorum nostrum, neque itaque temporibus quia quibusdam voluptates. Perferendis, eveniet cupiditate eaque id error laborum neque, necessitatibus nihil corrupti nulla similique numquam. Aliquid in laudantium placeat nisi non, quo consequuntur tenetur iusto exercitationem cumque rerum sapiente hic fuga sed dolores nihil consequatur iure tempore officia. Recusandae dicta obcaecati quidem, amet reiciendis iste deserunt. Doloribus, tenetur veritatis libero, voluptatibus architecto tempora fugit id dignissimos quod ex ullam ducimus quae inventore consectetur dicta odit assumenda voluptatem explicabo. Est nulla totam at ducimus in nesciunt doloribus cupiditate quisquam illum? Veritatis ducimus sunt cupiditate, perspiciatis aliquid, incidunt magni omnis vitae dolore exercitationem dolores, mollitia quos ipsa dicta. Omnis distinctio ducimus sunt hic tenetur, sit, dolore, ad autem nostrum atque consectetur exercitationem facilis eos quidem nulla laudantium perspiciatis aliquid accusantium deleniti. Asperiores ut cumque voluptates excepturi dolores aliquid expedita illum fugiat. Voluptatibus iure aut vel provident enim pariatur tenetur eius distinctio sint nam voluptate nisi placeat delectus dolor nobis quidem, consectetur laudantium saepe maiores expedita quae officia totam suscipit deleniti. Laborum consequuntur, sed voluptatem repellendus nesciunt explicabo atque architecto adipisci harum modi ea suscipit veritatis vero beatae repudiandae impedit vitae sequi veniam nulla iure perspiciatis dolore totam! At voluptatibus sunt iure voluptatum, quo animi? Odio, quidem voluptate aperiam omnis eum eos commodi porro praesentium blanditiis. Saepe impedit unde hic consequuntur eligendi nam accusantium. Earum, ipsa? Fugit a optio reiciendis, ipsum facere consectetur deleniti, eligendi adipisci praesentium cumque magnam repellendus earum qui hic voluptas ut enim, sapiente inventore iusto. Doloremque vel, voluptates optio molestiae sequi aliquam. In, ratione est voluptates, velit ipsum harum maiores fugit enim eos libero qui delectus quod ducimus sed eaque, dolore praesentium obcaecati soluta quo. Id, cumque rerum impedit incidunt, voluptates officiis nam est fugit temporibus aut nobis facere obcaecati a iste dolorum placeat delectus fuga. Suscipit sunt, nam nisi facere nihil in maxime voluptas beatae perferendis, assumenda fuga dolorem ab cumque esse consequuntur quos quae similique excepturi nemo. Eos ducimus cupiditate, facilis facere illo quos. Harum ipsa ullam dolorum architecto sapiente dicta maiores nisi numquam est, sit quos ipsum soluta officia incidunt cumque saepe provident consectetur ab nesciunt sint. Officiis magni ex nulla fugit dolor sunt, vero repellendus dolores, enim provident amet animi, nemo aut! Velit suscipit tempora sapiente quidem dolorum repudiandae cumque non minima impedit aliquam! Rerum repellendus, illum nam numquam ipsa eius quibusdam veritatis provident reprehenderit. Perferendis quos labore vitae voluptates distinctio aspernatur et! Repellat dolores officia dolorum deleniti voluptas atque eveniet, veritatis dolor numquam esse aut voluptates iusto consequuntur nemo magnam ex asperiores, modi omnis ea. Eaque quae saepe aut asperiores molestiae nemo! Repellendus mollitia reiciendis obcaecati explicabo asperiores nisi quisquam tempore, accusamus facilis hic quia unde.
    </div>
  </section>
</div>



<script src="<?= ASSETS_URL ?>/js/user/home.js"></script>
<?php
include INCLUDES_PATH . "/user/layout/footer.php";
?>