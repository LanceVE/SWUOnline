<?php

include_once 'MenuBar.php';
include "HostFiles/Redirector.php";
include_once "Libraries/PlayerSettings.php";
include_once 'Assets/patreon-php-master/src/PatreonDictionary.php';
include_once "APIKeys/APIKeys.php";

if (!empty($_SESSION['error'])) {
  $error = $_SESSION['error'];
  unset($_SESSION['error']);
  echo "<script>alert('" . $error . "')</script>";
}

$language = TryGet("language", 1);
$settingArray = [];
$defaultFormat = 0;
$defaultVisibility = (isset($_SESSION["useruid"]) ? 1 : 0);
if (isset($_SESSION["userid"])) {
  $savedSettings = LoadSavedSettings($_SESSION["userid"]);
  for ($i = 0; $i < count($savedSettings); $i += 2) {
    $settingArray[$savedSettings[intval($i)]] = $savedSettings[intval($i) + 1];
  }
  if (isset($_GET['language'])) {
    ChangeSetting("", $SET_Language, $language, $_SESSION["userid"]);
  } else if (isset($settingArray[$SET_Language])) $language = $settingArray[$SET_Language];
  if (isset($settingArray[$SET_Format])) $defaultFormat = $settingArray[$SET_Format];
  if (isset($settingArray[$SET_GameVisibility])) $defaultVisibility = $settingArray[$SET_GameVisibility];
}
$_SESSION['language'] = $language;
$isPatron = $_SESSION["isPatron"] ?? false;

$createGameText = ($language == 1 ? "Create Game" : "ゲームを作る");
$languageText = ($language == 1 ? "Language" : "言語");
$createNewGameText = ($language == 1 ? "Create New Game" : "新しいゲームを作成する");
$starterDecksText = ($language == 1 ? "Starter Decks" : "おすすめデッキ");
$deckUrl = TryGet("deckUrl", '');

$canSeeQueue = isset($_SESSION["useruid"]);

?>

<?php
include_once 'Header.php';
?>

<div class="core-wrapper">

  <div class="game-browser-wrapper">
    <div class="game-browser container bg-black" style='overflow-y:auto;'>
      <?php
      try {
        include "ServerChecker.php";
      } catch (\Exception $e) {
      }
      ?>
    </div>
  </div>

  <div class='create-game-wrapper'>
  <?php

  if (IsMobile()) echo ("<div class='create-game container bg-black' style='overflow-y:visible'>");
  else echo ("<div class='create-game container bg-black' style='overflow-y:auto'>");

  ?>

  <h2><?php echo ($createNewGameText); ?></h2>

  <?php
  echo ("<form style='width:100%;display:inline-block;' action='" . $redirectPath . "/CreateGame.php'>");

  $favoriteDecks = [];
  if (isset($_SESSION["userid"])) {
    $favoriteDecks = LoadFavoriteDecks($_SESSION["userid"]);
    if (count($favoriteDecks) > 0) {
      $selIndex = -1;
      if (isset($settingArray[$SET_FavoriteDeckIndex])) $selIndex = $settingArray[$SET_FavoriteDeckIndex];
      echo ("<div class='SelectDeckInput'>Favorite Decks");
      echo ("<select name='favoriteDecks' id='favoriteDecks'>");
      for ($i = 0; $i < count($favoriteDecks); $i += 4) {
        echo ("<option value='" . $i . "<fav>" . $favoriteDecks[$i] . "'" . ($i == $selIndex ? " selected " : "") . ">" . $favoriteDecks[$i + 1] . "</option>");
      }
      echo ("</select></div>");
    }
  }
  /*
  if (count($favoriteDecks) == 0) {
    echo ("<div><label class='SelectDeckInput'>" . $starterDecksText . ": </label>");
    echo ("<select name='decksToTry' id='decksToTry'>");

    echo ("</select></div>");
  }
  */

  ?>
  <div class='info-box'>
    <p>
      <img src="./Images/infoicon.png" alt="Info" style="width: 13px; height: 13px; margin: 0 2px -1px 0;">
      <span>Set 3 cards implementation is currently in progress. Please be patient while the card effects get coded. See this link for a list of cards not yet implemented:</span>
      <a href='https://github.com/SWU-Karabast/SWUOnline/tree/main/UnimplementedCards' target='_blank'>Unimplemented Cards</a>
    </p>
  </div>
  <label for="fabdb"><u><a style='color:lightblue;' href='https://www.swudb.com/' target='_blank'>SWUDB</a></u> or <u><a style='color:lightblue;' href='https://www.sw-unlimited-db.com/' target='_blank'>SW-Unlimited-DB</a></u> Deck Link <span class="secondary">(use the url or 'Deck Link' button)</span></label>
  <input type="text" id="fabdb" name="fabdb" value='<?= $deckUrl ?>'>
  <?php
  if (isset($_SESSION["userid"])) {
    echo ("<span class='save-deck'>");
    echo ("<labelfor='favoriteDeck'><input class='inputFavoriteDeck' type='checkbox' id='favoriteDeck' name='favoriteDeck' />");
    echo ("Save to Favorite Decks</label>");
    echo ("</span>");
  }
  ?>
  <label for="gameDescription" class='game-name-label'>Game Name</label>
  <input type="text" id="gameDescription" name="gameDescription" placeholder="Game #">

  <?php
  echo ("<label for='format' class='SelectDeckInput'>Format</label>");
  echo ("<select name='format' id='format' onchange='toggleInfoBox()'>");
  if ($canSeeQueue) {
    echo ("<option value='cc' " . ($defaultFormat == 0 ? " selected" : "") . ">Premier</option>");
    echo ("<option value='compcc' " . ($defaultFormat == 1 ? " selected" : "") . ">Request-Undo Premier</option>");
  }
  echo ("<option value='livinglegendscc'" . ($defaultFormat == 4 ? " selected" : "") . ">Open Format</option>");
  echo ("</select>");
  ?>

  <?php
  if ($canSeeQueue) {
    echo '<label for="public" class="privacy-label"><input class="privacy-input" type="radio" id="public" name="visibility" value="public" ' . ($defaultVisibility == 1 ? 'checked="checked"' : "") . '>';
    echo ('Public</label>');
  } else {
    echo '<p class="login-notice">&#10071;<a href="./LoginPage.php">Log In</a> to be able to create public games.</p>';
  }
  ?>
  <label for="private" class='privacy-label'>
    <input type="radio" class='privacy-input' id="private" name="visibility" value="private" <?php if ($defaultVisibility == 0) echo 'checked="checked"'; ?> />Private</label>
    <!--
  <label for="deckTestMode">
    <input class='SelectDeckInput' type="checkbox" id="deckTestMode" name="deckTestMode" value="deckTestMode">
    Single Player</label>
  -->
  <div style=' text-align:center;'>
    <input type="submit" class="create-game-button" value="<?php echo ($createGameText); ?>">
  </div>
  </form>

  </div>
</div>


<div class="karabast-column" >
  <div class="karabast-overview container bg-blue" >
    <p><b>Karabast is an open-source, fan-made platform.</b></p>
    <p>It is an educational tool only, meant to facilitate researching decks and strategies that is supportive of in-person play. As such, direct competition through the form of automated tournaments or rankings will not be added.</p>
    <p>This tool is free to use and is published non-commercially. Payment is not required to access any functionality.</p>
  </div>

  <div class="karabast-news container bg-black" style='<?php if (IsMobile()) echo ("display:none; "); ?>'>
    <h2>News</h2>
    <div style="position: relative;">
      <div style='vertical-align:middle; text-align:center;'>
        <!--<img src="./Images/droids.png" alt="SHD" style="max-width: 100%; border-radius: 5px;">
        <div style="text-align: left;">
        <h3 style="margin: 15px 0; display: block;">Set 3 cards and Karabast's next steps </h3>
        <p>For the last few months we've been hard at work on a brand new version of Karabast. Our new codebase will dramatically simplify future card implementation, and card error fixes. We're working hard to get this new version launched as soon as possible, but that will be after Set 3 releases. Set 3 cards will not be implemented until we make this transition.</p>

         <p> <a target="_blank" href="https://discord.gg/hKRaqHND4v">Join our Discord for the latest progress updates and timelines</a>. If you have coding experience we are always open to new contributors. Info on how to help out can also be found on <a target="_blank" href="https://discord.gg/hKRaqHND4v">Discord</a>. We appreciate everyone's patience as we work on this new Karabast experience, and look forward to playing games with you when we launch!</p>
        </div>-->
      </div>
    </div>
    <?php
    /*
    if (!$isPatron) {
      echo '<div>
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8442966023291783"
            crossorigin="anonymous"></script>
        <!-- MainMenu -->
        <ins class="adsbygoogle"
            style="display:block"
            data-ad-client="ca-pub-8442966023291783"
            data-ad-slot="5060625180"
            data-ad-format="auto"
            data-full-width-responsive="true"></ins>
        <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
      </div>';
    }
    */
    ?>
  </div>

  </div>

</div>
</div>

<script>
  function changeLanguage() {
    window.location.search = '?language=' + document.getElementById('languageSelect').value;
  }

  function toggleInfoBox() {
    var formatSelect = document.getElementById('format');
    var infoBox = document.getElementById('info-box');
    if (formatSelect.value === 'livinglegendscc') {
      infoBox.style.display = 'block';
    } else {
      infoBox.style.display = 'none';
    }
  }

  // Ensure the info box is displayed correctly based on the default selected format
  window.onload = function() {
    toggleInfoBox();
  };
</script>
<?php
include_once 'Disclaimer.php';
?>
