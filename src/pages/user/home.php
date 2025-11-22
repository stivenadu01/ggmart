<?php
$pageTitle = 'home';
include INCLUDES_PATH . "/user/layout/header.php";
?>

<div class="flex-1">

  <section
    class="relative w-full overflow-hidden md:px-10"
    x-data="carousel"
    x-init="init()">

    <!-- WRAPPER -->
    <div class="w-full h-fit relative">

      <!-- SLIDES -->
      <template x-for="(item, index) in hero" :key="index">
        <div
          x-show="current === index"
          x-transition.opacity.duration.500ms
          class="absolute inset-0">

          <img
            :src="`${uploadsUrl}${item.image_path}`"
            class="w-full h-full object-cover">
        </div>
      </template>

      <!-- LOADING -->
      <div
        x-show="loading"
        class="absolute inset-0 flex items-center justify-center bg-gray-200">
        <span>Loading...</span>
      </div>
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