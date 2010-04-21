<?php
  require_once('../../../wp-load.php');
  
  $auth = $_POST['auth'];
  $users_table = $wpdb->prefix . "users";
  $songs_table = $wpdb->prefix . "songs";
  
  $result = $wpdb->get_results("SELECT id FROM $users_table WHERE MD5(user_pass) = '$auth'");
  
  if($result)
  {
    $user_id = $result[0]->id;
    $title = $_POST['song']['title'];
    $artist = $_POST['song']['artist'];
    $album = $_POST['song']['album'];
    
    if($title && $artist && $album)
    {
      
      require("aws_signed_request.php");
            
      $search = "$artist, $album";
      
      $public_key = get_option("now_playing_amzn_public");
      $private_key = get_option("now_playing_amzn_private");
      $tag = get_option("now_playing_amzn_tag") ? get_option("now_playing_amzn_tag") : "mybl06a-20";
      
      $pxml = aws_signed_request("com", array("Operation"=>"ItemSearch","SearchIndex"=>"Music","ResponseGroup"=>"Small,Images", "Keywords" => $search, "AssociateTag" => $tag), $public_key, $private_key);
      
      if ($pxml === False)
      {
        die("problem inserting song");
      }
      else
      {
        if (isset($pxml->Items->Item->MediumImage->URL))
        {
          $img =  $pxml->Items->Item->MediumImage->URL;
          $url =  $pxml->Items->Item->DetailPageURL;  
        }
      }
      
      $insert = "INSERT INTO $songs_table (img, title, artist, album, url, user_id) VALUES ('$img','$title','$artist','$album','$url','$user_id')";
      $results = $wpdb->query( $insert );
      
      echo "song inserted";
    }
    else
      die("Missing required field");
    
  }
  else
  {
    die("bad credentials");
  }
  
  
?>