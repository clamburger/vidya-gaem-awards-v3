<?php
namespace VGA;

class Utils
{
    public static function convertSteamID($communityID)
    {
        $steamID = bcsub((string)$communityID, "76561197960265728");
        $steamID = $steamID / 2;
        if ($steamID == round($steamID)) {
            $steamID = "STEAM_0:0:".$steamID;
        } else {
            $steamID = "STEAM_0:1:".floor($steamID);
        }
        return $steamID;
    }

    public static function getAPIinfo($communityID)
    {
        $result = file_get_contents("https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=".STEAM_API_KEY."&steamids=$communityID");
        $json = json_decode($result);
        return $json->response->players[0];
    }

    public static function storeMessage($type, $string, $value = null)
    {
        $_SESSION['message'] = array($type, $string);
        if ($value !== null) {
            $_SESSION['message'][2] = $value;
        }
    }

    public static function refresh()
    {
        header("Location: https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
    }

    public static function returnJSON($result, $info = true, $extraData = [])
    {
        echo json_encode(array_merge($extraData, [$result => $info]));
        exit;
    }

    public static function action($action, $firstID = null, $secondID = null)
    {
        global $ID, $PAGE, $mysql;

        $query = "INSERT INTO `actions` (`UserID`, `Timestamp`, `Page`, `Action`,
            `SpecificID1`, `SpecificID2`) VALUES(?, NOW(), ?, ?, ?, ?)";
        $stmt = $mysql->prepare($query);
        $stmt->bind_param('sssss', $ID, $PAGE, $action, $firstID, $secondID);
        $stmt->execute();
    }
}

