<div id="contest-title" class="flex" style="position:sticky;top: 0;background-color: black;padding-left: 20px;justify-content: space-between;height:90px;">
  <div class="flex">
    <h1 class="contest-type" class="flex" style="color:white"><?=$contest_name?></h1>
  </div>
  <div class="flex">
    <ul id="contest-status" class="flex" style="display: flex;justify-content: center;align-items: center;padding: 10px;box-sizing: border-box;">
      <li style="color:white;display: <?=$status==TSC::STATUS_UPCOMING?"flex":"none" ?>;justify-content: space-between;align-items: center;box-sizing: border-box;"><h3>コンテスト開始</h3><h3 style="padding-left:10px;"><?=date("Y/m/d H:i:s", $start), "(JST)";?></h3></li>
      <li style="color:white;display: <?=$status==TSC::STATUS_ONGOING?"flex":"none" ?>;justify-content: space-between;align-items: center;box-sizing: border-box;"><h3>コンテスト開催中</h3><h3 style="padding-left:80px;">終了</h3><h3 style="padding-left:10px;"><?=date("Y/m/d H:i:s", $end), "(JST)";?></h3></li>
      <li style="color:white;display: <?=$status==TSC::STATUS_ENDED?"flex":"none" ?>;justify-content: space-between;align-items: center;box-sizing: border-box;"><h3>終了済み</h3><h3 style="padding-left:80px;">ディスカッション開始</h3><h3 style="padding-left:10px;"><?=date("Y/m/d H:i:s", $discussion), "(JST)-";?></h3></li>
      <li style="color:white;display: <?=$status==TSC::STATUS_DISCUSSION?"flex":"none" ?>;justify-content: space-between;align-items: center;box-sizing: border-box;"><h3>ディスカッション中</h3></li>
      <li style="color:white;display: <?=$status==TSC::STATUS_ARCHIVED?"flex":"none" ?>;justify-content: space-between;align-items: center;box-sizing: border-box;"><h3>終了済み</h3></li>
    </ul>
  </div>
</div>