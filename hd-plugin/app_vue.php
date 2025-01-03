<?php

$lang = get_locale();
$user_id = get_current_user_id();
?>
<div style="width:100%; grid-column: span 3 / span 3;">

   <!-- <script type="module" src="http://localhost:3006/src/main.ts"></script> -->
   <script type="module" crossorigin src="<?php echo plugin_dir_url(__FILE__) . 'frontend/dist/assets/index-1a9c52af.js' ?>"></scriscri>
   <link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__) . 'frontend/dist/assets/index-aca1db87.css'; ?>">
   <script>
   </script>
   <div id="app" data-lang="<?php echo esc_attr($lang); ?>" data-user-id="<?php echo esc_attr($user_id); ?>">
      <style>
         .loading-wrap {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 90vh;

         }

         .spinner {
            --s: 9px;
            width: 56px;
            height: 56px;
            background: #208000;
            border-radius: 50%;
            animation: spinner-ehcge9 2s infinite linear;
            clip-path: polygon(0 0, calc(50% - var(--s)) 0, 50% var(--s), calc(50% + var(--s)) 0, 100% 0, 100% calc(50% - var(--s)), calc(100% - var(--s)) 50%, 100% calc(50% + var(--s)), 100% 100%, calc(50% + var(--s)) 100%, 50% calc(100% - var(--s)), calc(50% - var(--s)) 100%, 0 100%, 0 calc(50% + var(--s)), var(--s) 50%, 0 calc(50% - var(--s)));
         }

         @keyframes spinner-ehcge9 {
            100% {
               transform: rotate(1turn);
            }
         }
      </style>
      <div class="loading-wrap">
         <div class="spinner"></div>
      </div>

   </div>
</div>