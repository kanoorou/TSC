<script setup lang="ts">
  useHead({
    title:null
    }
  );
</script>
<template>
    <main>
      <div id="contents-wrapper" class="container-lg text-start mx-auto my-5">
      <div class="row">
        <h1 class="text-center">TSCへようこそ！</h1>
        <div class="col-lg-8 py-2 px-5">
        <h5>全合成グランプリ(TSC)では、有機化合物の合成経路の質を競います。<br>ゲーム感覚で有機化学を楽しみながら、学びを深めてみませんか？</h5>
        </div>
        <div class="col-lg-4 py-2">
        <div  class="row p-1">
                    <div class="col-6 col-lg-12 large-button"><a id="learn-more-btn" href="/about">TSCについてもっと知る</a></div>
                    <div class="col-6 col-lg-12 large-button"><a id="participate-btn" href="/register">コンテストに参加する</a></div>
                </div>
        </div>
      </div>
      <div class="row mt-5">
        <div class="col-6 col-lg-4"><h3 class="text-center">開催予定のコンテスト</h3></div>
        <div class="col-6 col-lg-4"><h2 class="text-center">開催済のコンテスト</h2></div>
        <div class="col-12 col-lg-4"><h2 class="text-center">お知らせ</h2></div>
      </div>
      </div>
    </main>
</template>
<style>
#learn-more-btn{
        background-color: coral;
        color: aliceblue;
        border:1px solid #002060;
        margin-bottom:0.5rem;
}
#learn-more-btn:hover{
    background-color: powderblue;
    color: #002060;
}
#participate-btn{
    background-color: coral;
    color: aliceblue;
    border:1px solid #002060 ;
}
#participate-btn:hover{
    background-color: powderblue;
    color: #002060;
}
</style>
<!--READD <script src="/js/common.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script> READD-->

