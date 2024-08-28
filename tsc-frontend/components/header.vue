<script setup>
    const route = useRoute();
    // console.log('current path:', route.path)
    let isHome=false,isContest=false,isLb=false;
    switch (route.path){
        case '/':
            isHome=true;
            break;
        case '/contest/':
            isContest=true;
            break;
        case '/leaderboard/':
            isLb=true;
            break;
    }
</script>
<template>
    <header>
        <div id="header-main" class=" container-fluid flex-wrap justify-content-center mb-1">
        <div class="row overflow-visible ">
            <a id="header-icon" class="col-md-10 col-lg-9 d-block d-sm-inline-flex align-items-center mb-3 mb-md-0 text-center" href="/">
                <img id="header-icon-img" src="/assets/img/header-logo.png" alt="TSC">
                <h1 id="header-title">全合成グランプリ</h1>
            </a>
            <div id="header-lang" class="col-sm-2 col-lg-1 d-none d-md-block d-md-inline-flex align-items-center justify-content-around text-center">
                    <div class="current"><a href="/">JP</a></div>
                    <div class=""><a href="/">EN</a></div>
            </div>
            <div id="header-menu" class="col-sm-12 col-lg-2">
                <div id="header-login" class="row p-1"><!-- Hide if logged in-->
                    <div class="col-6 col-lg-12 large-button"><a href="/login">ログイン</a></div>
                    <div class="col-6  col-lg-12 large-button"><a href="/register">新規登録</a></div>
                </div>
	 	        <div id="header-mypage" class="row p-1" style="display:none;"><!--Show if logged in-->
                 <div class="col-6 col-lg-12"><span class="d-block py-2 text-center">ログイン中</span></div><!-- ユーザー名等表示-->
                 <div class="col-6 col-lg-12"><a href="/mypage">マイページ</a></div>    
                </div>
            </div>
        </div>
        
</div>   
<div class="container-fluid">       
    <div class="row">
        <!-- <h1><?php echo($_SERVER['REQUEST_URI']); ?> </h1> Debugging-->
    <nav class="header-bottom navbar navbar-expand-md py-0">
        <div id="participate" class="nav-item px-3" :if="!isContest"><a class="nav-link" href="/contest/">コンテストに参加</a></div>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main-menu-collapse" aria-controls="main-menu-collapse" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="main-menu-collapse">
            <ul id="main-menu" class="w-100 justify-content-center navbar-nav">
                <li class="nav-item" :class="{current: isHome}"><a class="nav-link" aria-current="page" href="/">HOME</a></li><!-- currentクラスの指定方法要検討 -->
                <li class="nav-item" :class="{current: isContest}"><a class="nav-link" href="/contest">コンテスト</a></li>
                <li class="nav-item" :class="{current: isLb}"><a class="nav-link" href="/leaderboard/">ランキング</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="/" id="dropdown01" data-bs-toggle="dropdown" aria-expanded="false">学習</a>
                    <ul class="dropdown-menu" aria-labelledby="dropdown01">
                        <li><a class="dropdown-item" href="#">TSC反応ガイド</a></li>
                        <li><a class="dropdown-item" href="#">初心者向け</a></li>
                        <li><a class="dropdown-item" href="#">過去問</a></li>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="/">問い合わせ</a></li>
                <li class="nav-item d-flex d-md-none">
                    <div class="current w-50"><a class="nav-link" href="/">JP</a></div>
                    <div class="w-50"><a class="nav-link" href="/">EN</a></div>
                </li>
                <!-- lang for small -->
            </ul>
            </div>           
        </nav>
    </div> 
</div>      
</header>
</template>

