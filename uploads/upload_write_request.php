<?php

if($_SERVER['REQUEST_METHOD']=='POST'){

  $image = $_POST['image'];
  $path = $_POST['url'];

  // Convert the url to local dir, since we don't know the local ip, we try to replace all our server ips.
  $prefix_1 = 'http://50.18.207.106/';
  $prefix_2 = 'http://54.223.152.54/';
  $replace_with = '/var/www/html/';
  $dest_dir = str_replace($prefix_1, $replace_with, $path);
  $dest_dir = str_replace($prefix_2, $replace_with, $dest_dir);

  // Generate the copy_to_remote dir. The dir holds images that should be copied to other servers.
  $copy_dir = str_replace('/uploads/uploads/', '/uploads/copy_to_remote/', $dest_dir);

  // Create folders if they do not exist.
  if (!is_dir('uploads')) {
    mkdir('uploads');
    chmod('uploads', 775);
  }
  if (!is_dir('copy_to_remote')) {
    mkdir('copy_to_remote');
    chmod('copy_to_remote', 775);
  }

  // Put files
  $image_data = base64_decode($image);
  file_put_contents($dest_dir, $image_data);
  file_put_contents($copy_dir, $image_data);
  echo('Photo upload succeed');
}else{
  echo "Error during photo uploading";
}


?>
