<?php
$config = require '../src/config.php';

/**
 * Véletlenszám generálását végzi
 * @param float   $st_num       alsó határ
 * @param float   $end_num      felső határ
 * @param int     $mul          szorzó
 */
function rand_float($st_num = 0, $end_num = 1, $mul = 100000)
{
  if ($st_num > $end_num) return false;
  return mt_rand($st_num * $mul, $end_num * $mul) / $mul;
}

/**
 * Új árfolyamok generálását végzi véletlenszámok használatával
 * @param float $currentrate az aktuális árfolyam-érték
 */
function generate_new_rates($currentrate)
{
  $generated_rate = rand_float($currentrate * 0.9, $currentrate * 1.05, 100000) . "\n";
  return ($generated_rate);
}

/**
 * Az aktuális árfolyamok kiolvasásást végzi
 * @param string $table_name a valuták illetve devizák táblaneve
 */
function getCurrentCurrencyRates($table_name)
{
  global $config;
  $conn = mysqli_connect($config["DB_HOST"], $config["DB_USER"], $config["DB_PASS"], $config["DB_DATABASE"]);
  if (!$conn) {
    exit("Connection failed: " . mysqli_connect_error());
  }
  $sql = "SELECT id, name, buy, sell FROM " . $table_name;
  $result = mysqli_query($conn, $sql);
  mysqli_close($conn);
  return $result;
}

/**
 * A valuta-és devizaárfolyamok frissítését végzi
 * @param int     $percentage       az eladási- és a vételi árfolyam közötti különbség értéke
 * @param string  $table_name       a valuták illetve devizák táblaneve
 * @param array   $mysqli_result    a jelenlegi árfolyamok
 */
function updateCurrentCurrencyRatesWithRandomValues($table_name, $result, $percentage)
{
  global $averagecurrencyrates;
  global $config;
  $dt = new DateTime("now", new DateTimeZone('Europe/Budapest'));
  $current_time = $dt->format('Y/m/d H:i:s');
  if (mysqli_num_rows($result) > 0) {
    $conn = mysqli_connect($config["DB_HOST"], $config["DB_USER"], $config["DB_PASS"], $config["DB_DATABASE"]);
    if (!$conn) {
      exit("Connection failed: " . mysqli_connect_error());
    }
    $i = 0;
    while ($row = mysqli_fetch_assoc($result)) {
      $actual_id = $row["id"];
      $new_buy = $averagecurrencyrates[$i] * (100 - $percentage) / 100;
      $new_sell = $averagecurrencyrates[$i] * (100 + $percentage) / 100;
      $i = $i + 1;
      $sql = "UPDATE " . $table_name . " SET buy=" . $new_buy . ", sell=" . $new_sell . ", validfrom= '" . $current_time . "' WHERE id=" . $actual_id . ";";
      if (mysqli_query($conn, $sql)) {
        echo "Table: " . $table_name . " - " . $row["name"] . " Buy: " . $new_buy . " Sell: " . $new_sell . " - exchange rate refresh succesful!<br />";
      } else {
        echo "Unsuccessful exchange rate refresh!<br />";
      }
    }
    mysqli_close($conn);
  }
}

const AVERAGE_CURRENCY_RATES = array(373.0000000, 339.0000000, 447.0000000, 255.0000000, 190.0000000, 272.0000000, 362.0000000, 15.000000, 50.0000000, 49.0000000, 272.0000000, 39.00000000, 79.0000000, 75.0000000, 3.2000000, 3.4000000, 36.000000); // EUR,USD,GBP,AUD,BGN,CAD,CHF,CZK,DKK,HRK,JPY,NOK,PLN,RON,RSD,RUB,SEK
$averagecurrencyrates = array();
for ($i = 0; $i < count(AVERAGE_CURRENCY_RATES); $i++) {
  array_push($averagecurrencyrates, generate_new_rates(AVERAGE_CURRENCY_RATES[$i]));
}
$result = getCurrentCurrencyRates("currencies");
updateCurrentCurrencyRatesWithRandomValues("currencies", $result, 3);
echo "<br />";
$result = getCurrentCurrencyRates("foreigncurrencies");
updateCurrentCurrencyRatesWithRandomValues("foreigncurrencies", $result, 1);
