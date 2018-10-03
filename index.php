
<?php


class c_channel_analytics
{
    public $m_channel_analytics;

    /**
     * c_channel_analytics constructor.
     */
    public function __construct()
    {
        $maxResults = 25;

        $main_menu = "video_manager";
        if (file_exists('model/m_channel_analytics.php')) {
            include 'model/m_channel_analytics.php';

        }
        $this->m_channel_analytics = new m_channel_analytics();
        //$this->m_channel = new m_channel();
        $destination_path = "api_json";
        $sourceJsonPath="../youtube/admin/YoutubeApi/ClientSecretFile/";

        $Id = 0;
        if (isset($_GET["Id"])) {
            $Id = $_GET["Id"];
        }
        if (isset($_GET["videoID"])) {
            $videoID = $_GET["videoID"];
        }

        if (isset($_GET["ChannelIDSearch"])) {
            $ChannelID = $_GET["ChannelIDSearch"];
        }
        $arr_channel = $this->m_channel_analytics->GetAllChannel();
        foreach ($arr_channel as $channel) {
            $jsonFile = $channel['FromJsonFile'];
            $accessToken = $channel['AccessToken'];
            $refreshToken = $channel['RefreshToken'];
            $jsonPath = $sourceJsonPath . basename(dirname($channel['FromJsonFile'])) . "/" . "client_secret.json";

            if (!file_exists($destination_path . "/" . $channel['ChannelID'])) {
                mkdir($destination_path . "/" . $channel['ChannelID'], 700);
                copy($jsonPath, $destination_path . "/" . $channel['ChannelID'] . "/" . basename($jsonFile));
                $apikey = "{\"access_token\":\"$accessToken\",\"token_type\":\"Bearer\",\"expires_in\":3600,\"created\":1528072583,\"refresh_token\":\"$refreshToken\"}";
                $tokenName = "Google.Apis.Auth.OAuth2.Responses.TokenResponse-channel";
                file_put_contents($destination_path . "/" . $channel['ChannelID'] . "/" . $tokenName, $apikey);
            }
            $authConfigFile = $destination_path . "/" . $channel['ChannelID'] . "/" . "client_secret.json";
            $credentialsPath = $destination_path . "/" . $channel['ChannelID'] . "/" . "Google.Apis.Auth.OAuth2.Responses.TokenResponse-channel";
            require_once 'YoutubeApi/vendor/autoload.php';

            $client = $this->getClient($authConfigFile, $credentialsPath);
            // Define an object that will be used to make all API requests.
            try {
                $analytics = new Google_Service_YouTubeAnalytics($client);
            } catch (Exception $exception) {

            }
            // $client is your Google_Client object

            // here we set some params
//                        date_default_timezone_set('Asia/Bangkok');

            $now = new DateTime();
            $backDate = $now->sub(DateInterval::createFromDateString('30 days'))->format('Y-m-d');
            $backDate = trim(str_replace("  ", " ", $backDate));

            date_default_timezone_set('Asia/Bangkok');
            $current_date = date('Y-m-d ') . trim('');
            $current_date = trim(str_replace("  ", " ", $current_date));
            $createDate = date('Y-m-d H:i:s');

            //get youtube report
            $id = 'channel==' . $channel['ChannelID'];
            $totalReport = array(
                'dimensions' => 'channel'
            );
            $monthlyReport = array(
                'dimensions' => '30DayTotals'
            );

            $revenueChannelReport="";
            try {
                $revenueChannelReport = $analytics->reports->query($id, $backDate, $current_date, "estimatedRevenue", $totalReport);
            }
            catch (Exception $exception)
            {

            }

            try {
                $totalChannelReport = $analytics->reports->query($id, $backDate, $current_date, "views,likes,comments,subscribersGained", $totalReport);
                $monthlyChannelReport = $analytics->reports->query($id, $backDate, $current_date, "views,likes,comments,subscribersGained", $monthlyReport);
                $checkChannel = $this->m_channel_analytics->CheckChannelAnalytics($channel['ChannelID']);
                if ($checkChannel == null) {

                    if($revenueChannelReport!="")
                    {
                        $this->m_channel_analytics->InsertChannelAnalytics($channel['ChannelID'], json_encode($totalChannelReport['rows']),json_encode($monthlyChannelReport['rows']),json_encode($revenueChannelReport['rows']), $createDate);
                    }
                    else
                    {
                        $this->m_channel_analytics->InsertChannelAnalytics($channel['ChannelID'], json_encode($totalChannelReport['rows']),json_encode($monthlyChannelReport['rows']),$revenueChannelReport, $createDate);
                    }

                } else {
                    if($revenueChannelReport!="")
                    {
                        $this->m_channel_analytics->UpdateChannelAnalytics($channel['ChannelID'], json_encode($totalChannelReport['rows']), json_encode($monthlyChannelReport['rows']),json_encode($revenueChannelReport['rows']), $createDate);
                    }
                    else
                    {
                        $this->m_channel_analytics->UpdateChannelAnalytics($channel['ChannelID'], json_encode($totalChannelReport['rows']), json_encode($monthlyChannelReport['rows']),$revenueChannelReport, $createDate);
                    }
                }
            }
            catch (Exception $exception)
            {

            }
        }
    }
    function getClient($authConfigFile,$credentialsPath) {
        $client = new Google_Client();
        $client->setAuthConfigFile($authConfigFile);
        $client->setRedirectUri('http://localhost/ytbweb/');
        $client->addScope('https://www.googleapis.com/auth/youtube.force-ssl https://www.googleapis.com/auth/youtube https://www.googleapis.com/auth/yt-analytics-monetary.readonly https://www.googleapis.com/auth/youtubepartner https://www.googleapis.com/auth/youtube.upload');
        $client->setAccessType("offline");
        $client->setIncludeGrantedScopes(true);
        $client->setApprovalPrompt('force');
        $client->setApplicationName('Youtube Smart Tools');
        // Load previously authorized credentials from a file.

        if (file_exists($credentialsPath))
        {
            $unserArray =  file_get_contents($credentialsPath);
            $accessToken = json_decode($unserArray,true);
            //$accessToken = json_decode(file_get_contents($credentialsPath), true);

        }
        else
        {
            $redirect_uri = 'http://localhost/ytbweb/oauth2callback.php';
            header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
        }
        $client->setAccessToken($accessToken);

        // Refresh the token if it's expired.
        if ($client->isAccessTokenExpired())
        {
            $refreshTokenSaved = $client->getRefreshToken();
            if($refreshTokenSaved != null)
            {
                //  printf('update access token		');
                $client->fetchAccessTokenWithRefreshToken($refreshTokenSaved);
                //printf('%cpass access token to some variable',10);
                $accessTokenUpdated = $client->getAccessToken();
                //  print('append refresh token		');
                $accessTokenUpdated['refresh_token'] = $refreshTokenSaved;
                //  print('save to file		');
                file_put_contents($credentialsPath, json_encode($accessTokenUpdated));

            }
            else
            {
                //  printf("Get Refresh token fail%c",10);
            }


            //$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            //file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
        }
        return $client;
    }
    function uploadMedia($client, $request, $filePath, $mimeType) {
        // Specify the size of each chunk of data, in bytes. Set a higher value for
        // reliable connection as fewer chunks lead to faster uploads. Set a lower
        // value for better recovery on less reliable connections.
        $chunkSizeBytes = 1 * 1024 * 1024;

        // Create a MediaFileUpload object for resumable uploads.
        // Parameters to MediaFileUpload are:
        // client, request, mimeType, data, resumable, chunksize.
        $media = new Google_Http_MediaFileUpload(
            $client,
            $request,
            $mimeType,
            null,
            true,
            $chunkSizeBytes
        );
        $media->setFileSize(filesize($filePath));


        // Read the media file and upload it chunk by chunk.
        $status = false;
        $handle = fopen($filePath, "rb");
        while (!$status && !feof($handle)) {
            $chunk = fread($handle, $chunkSizeBytes);
            $status = $media->nextChunk($chunk);
        }

        fclose($handle);
        return $status;
    }

    /***** END BOILERPLATE CODE *****/

    // Sample php code for thumbnails.set

    function thumbnailsSet($client, $service, $media_file, $params) {
        $params = array_filter($params);
        $client->setDefer(true);
        $request = $service->thumbnails->set(join(',', $params));
        $client->setDefer(false);
        return $response =$this-> uploadMedia($client, $request, $media_file, 'image/png');
        //print_r($response);
    }

}
$c_channel_analytics = new c_channel_analytics();
?>