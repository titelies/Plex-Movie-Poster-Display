<?php
include 'config.php';
$results = Array();
$movies = Array();

#Display Custom Image
if ($customImageEnabled == "Yes") {
  $title = "<br /><p style='font-size: 80px; -webkit-text-stroke: 2px yellow;'> &nbsp; </p>";
  $display = "<img src='$customImage' style='width: 100%'>";
  $info = "<p style='font-size: 65px; -webkit-text-stroke: 2px yellow;'> &nbsp; </p>";
} else {

  #Plex Module
  $url     = 'http://'.$plexServer.':32400/status/sessions?X-Plex-Token='.$plexToken.'';
  $getxml  = file_get_contents($url);
  $xml 	 = simplexml_load_string($getxml) or die("feed not loading");
  $title   = NULL;
  $display = NULL;
  $info    = NULL;

  if ($xml['size'] != '0') {
      foreach ($xml->Video as $clients) {
          if(strstr($clients->Player['address'], $plexClient)) {
                    
            if(strstr($clients['type'], "movie")) {
            	$art = $clients['thumb'];

                $poster = explode("/", $art);
                $poster = trim($poster[count($poster) - 1], '/');
                $filename = '/cache/' . $poster;

                if (file_exists($filename)) {
                    #Future Code Coming
                } else {
                    file_put_contents("cache/$poster", fopen("http://$plexServer:32400$art?X-Plex-Token=$plexToken", 'r'));
                }

                $title = "<br /><p style='font-size: 65px; -webkit-text-stroke: 2px yellow;'> $nowShowingTopText </p>";
                $display = "<img src='cache/$poster' style='width: 100%'>";
                $info = "<p style='font-size: 30px;'>" . $clients['summary'] . "</p>";
	    }

            if(strstr($clients['type'], "episode")) {
                $art = $clients['grandparentThumb'];
 
                $poster = explode("/", $art);
                $poster = trim($poster[count($poster) - 1], '/');
                $filename = '/cache/' . $poster;

                if (file_exists($filename)) {
                    #Future Code Coming
                } else {
                    file_put_contents("cache/$poster", fopen("http://$plexServer:32400$art?X-Plex-Token=$plexToken", 'r'));
                }

                $title = "<br /><p style='font-size: 65px; -webkit-text-stroke: 2px yellow;'> $nowShowingTopText </p>";
                $display = "<img src='cache/$poster' style='width: 100%'>";
                $info = "<p style='font-size: 30px;'>Episode: " . $clients['title'] . " - " . $clients['summary'] . "</p>";
           }
        }
     }
  }

  #If Nothing is Playing
  if ($display == NULL) {
    $title = "<br /><p style='font-size: 90px; -webkit-text-stroke: 2px yellow;'> $comingSoonTopText </p>";
   
    $UnWatchedMoviesURL = 'http://'.$plexServer.':32400/library/sections/'.$plexServerMovieSection.'/all?X-Plex-Token='.$plexToken.'';
    $getMovies  = file_get_contents($UnWatchedMoviesURL);
    $xmlMovies = simplexml_load_string($getMovies) or die("feed not loading");
    $countMovies = count($xmlMovies);

    if ($countMovies > '0') {
      foreach ($xmlMovies->Video as $movie) {
        $movies[] = strip_tags($movie['title']);
      }

      $random_keys = array_rand($movies,1);
      $showMovie = $movies[$random_keys];

      foreach ($xmlMovies->Video as $movie) {
         if(strstr($movie['title'], $showMovie)) {
           $art = $movie['thumb'];
  
           $poster = explode("/", $art);
           $poster = trim($poster[count($poster) - 1], '/');
           $filename = 'cache/' . $poster;

           if (file_exists($filename)) {
              #Future Code Coming
           } else {
              file_put_contents("cache/$poster", fopen("http://$plexServer:32400$art?X-Plex-Token=$plexToken", 'r'));
           }

           $display = "<img src='cache/$poster' style='width: 100%'>";
         }
      }
    }
 
    $info = "<br /><p style='font-size: 75px; -webkit-text-stroke: 2px yellow;'> $comingSoonBottomText </p>";
  }
}

$results['top'] = "$title";
$results['middle'] = "$display";
$results['bottom'] = "$info";

echo json_encode($results);
?>
