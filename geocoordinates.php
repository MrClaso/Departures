<?php
header("Content-type: text/plain; charset=UTF-8");
$x = $_POST['x'];
$y = $_POST['y'];

echo "Din plats:" . "<br>";
echo "x = " . $x . "<br>";
echo "y = " . $y . "<br>";

$x = $x * 1000000;
$y = $y * 1000000;

/*
    "Name": "Rönninge (Salem)",
    "SiteId": "9523",
    "Type": "Station",
    "X": "17749596",
    "Y": "59194111",
   */

$dist = sqrt(($x - 17749596) * ($x - 17749596) + ($y - 59194111) * ($y - 59194111)) / 10000;

echo "<br>";
echo "Avståndet till Rönninge station är " . sprintf("%1\$u", $dist) . " km";
echo "<br>";
echo "<br>";

$ok = false;
if ($dist < 1.3) {
    $siteId = 9523;
    $transportation = "Trains";
    $ok = true;
}

/*
    "Name": "Tekniska högskolan (Stockholm)",
    "SiteId": "9204",
    "Type": "Station",
    "X": "18071707",
    "Y": "59345543",
    */

$dist = sqrt(($x - 18071707) * ($x - 18071707) + ($y - 59345543) * ($y - 59345543)) / 10000;

echo "Avståndet till Tekniska högskolan är " . sprintf("%1\$u", $dist) . " km";
echo "<br>";
echo "<br>";
if(!ok){
if ($dist < 1.3) {
    // Tekniska
    $siteId = "9204";
    $transportation = "Metros";
    $ok = true;
}
}
if ($ok) {
    $url = "https://api.sl.se/api2/realtimedeparturesV4.json?key=92dce9d3588142c49a21345f037dff2a&siteid=" . $siteId . "&Bus=False&Tram=False&timewindow=60";

    $json = file_get_contents($url);
    //    echo $json;

    $data = json_decode($json, true);

    $numcalls = 0;
    while (($data["StatusCode"] != 0) and ($numcalls < 5)) {
        sleep(1);
        $json = file_get_contents($url);
        $data = json_decode($json, true);
        $numcalls++;
    }
    //    var_dump($data);

    if ($data["StatusCode"] != 0) {
        echo "Kunde inte hämta data.";
    } else {

        $trains = $data["ResponseData"][$transportation];
        $spd = $data["ResponseData"]["StopPointDeviations"];

        foreach ($spd as $happening) {
            echo "Nivå för aktuell avvikelse. 0-9 där 9 är mest allvarlig.  " . $happening["Deviation"]["ImportanceLevel"] . "<br>";
            echo "Konsekvensbeskrivning för aktuell avvikelse.  " . $happening["Deviation"]["Consequence"] . "<br>";
            echo "Beskrivning av aktuell avvikelse.  " . $happening["Deviation"]["Text"] . "<br>";
            echo "<br>";
        }

        echo "<br>";

        foreach ($trains as $row) {
            if ($row["JourneyDirection"] == 2) {
                echo $row["Destination"] . "<br>";
                $date = date_create($row["TimeTabledDateTime"]);
                echo "Avgångstid enligt tidtabell : " . date_format($date, "H:i") . "<br>";
                $date = date_create($row["ExpectedDateTime"]);
                echo "Förväntad avgångstid : " . date_format($date, "H:i") . "<br>";
                echo $row["DisplayTime"] . "<br>";
                echo "<br>";
                if (!is_null($row["Deviations"])) {
                    $dev = $row["Deviations"];
                    foreach ($dev as $d) {
                        echo "Nivå för aktuell avvikelse. 0-9 där 9 är mest allvarlig.  " . $d["ImportanceLevel"] . "<br>";
                        echo "Konsekvensbeskrivning för aktuell avvikelse.  " . $d["Consequence"] . "<br>";
                        echo "Beskrivning av aktuell avvikelse.  " . $d["Text"] . "<br>";
                        echo "<br>";
                    }
                }
                echo "------------------------------------";
                echo "<br>";
            }
        }
    }
} else {
    echo "Du är för långt från någon station" . "<br>";
}
