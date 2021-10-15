<?php

namespace App\Classes;

class MimeType {

  const DEFAULT_TYPE = "Otro";

  public static function get_default_type(){
    return self::DEFAULT_TYPE;
  }

  public static function discovery($mimetype){
    $categories = array("Documento" => self::get_document_mimetypes(),
                    "Imagen" => self::get_image_mimetypes(),
                    "Audio" => self::get_audio_mimetypes(),
                    "Video" => self::get_video_mimetypes(),
                    "Archivo comprimido" => self::get_file_mimetypes(),
                    "Texto" => self::get_text_mimetypes());
    $type = self::DEFAULT_TYPE;
    foreach($categories as $category_name => $elements){
      if(in_array($mimetype, $elements)){
        $type = $category_name;
        break;
      }
    }
    return $type;
  }

  public static function availableCategories(){
      return ['Documento','Imagen','Audio','Video', 'Texto', 'Archivo comprimido', 'Otro'];
  }

  public static function get_document_mimetypes(){
    $documents = ["application/pdf","application/x-abiword","application/octet-stream","application/msword",
                  "application/vnd.oasis.opendocument.presentation", "application/vnd.oasis.opendocument.spreadsheet",
                  "application/vnd.oasis.opendocument.text","application/vnd.ms-powerpoint","application/vnd.visio",
                  "application/vnd.ms-excel", "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
                  "application/vnd.openxmlformats-officedocument.wordprocessingml.template",
                  "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                  "application/vnd.openxmlformats-officedocument.presentationml.presentation",
                  "application/vnd.openxmlformats-officedocument.presentationml.slideshow",
                  "application/vnd.oasis.opendocument.text",
                  "application/vnd.ms-excel.sheet.macroEnabled.12",
                  "application/vnd.ms-powerpoint.presentation.macroEnabled.12",
                  "application/x-wine-extension-pdf",
                  "application/vnd.openxmlformats-officedocument.presentationml.template",
                  "application/wps-office.pdf",
                  "application/wps-office.docx"];
    return $documents;
  }

  public static function get_image_mimetypes(){
    $images = ["image/jpeg","image/png","image/gif","image/x-icon","image/svg+xml","image/webp","audio/3gpp",
               "audio/3gpp2", "image/tiff", "image/bmp", "image/heic"];
    return $images;
  }

  public static function get_audio_mimetypes(){
    $audio = ["audio/aac","audio/midi","audio/ogg","audio/x-wav","audio/webm", "audio/mpeg",
              "audio/x-m4a", "audio/mp4", "audio/*", "audio/mp3", "audio/wav", "audio/m4a",
              "audio/x-ms-wma", "audio/vnd.dlna.adts", "audio/amr", "audio/x-flac"];
    return $audio;
  }
  public static function get_video_mimetypes(){
    $video = ["video/mp4","video/x-msvideo","video/mpeg","video/ogg","video/webm","video/3gpp",
              "video/3gpp2", "video/quicktime", "video/*", "video/x-ms-wmv", "video/x-matroska",
              "video/avi", "video/webm"];
    return $video;
  }

  public static function get_file_mimetypes(){
    $files = ["application/x-rar-compressed", "application/zip","application/x-bzip","application/x-bzip2",
              "application/x-7z-compressed", "application/x-zip-compressed", "application/gzip"];
    return $files;
  }

  public static function get_text_mimetypes(){
    $text = ["text/csv","text/plain","text/html","text/calendar","application/rtf","application/xhtml+xml",
             "text/css", "application/json", "application/xml", "text/xml", "application/rtf"];
    return $text;
  }
}
