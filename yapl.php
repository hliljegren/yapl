<?php
/*
Plugin Name: Y.A.P.L
Description: Yet Another Post Lister, but bring your own css. This plugin creates a listing of any page type via the shortcode [yapl]. Normal usage is [yapl category="category" display_items="image,title,content"] but there are a lot of attributes you can set. See <a href="https://github.com/hliljegren/yapl">Readme</a> for documentation.
Version: 0.8.6
Author: Håkan Liljegren
*/
if (!function_exists("add_action")) {
  die("You are trying to access this file in a manner not allowed.");
}

function yapl_shortcode_handler($args, $content = null)
{
  if (is_feed()) {
    return "";
  }
  $flags = [
    "type" => "post",
    "category" => false,
    "category_join" => "or",
    "author" => false,
    "not_in_category" => false,
    "post_id" => false,
    "meta" => false,
    "link_title" => true,
    "link_categories" => true,
    "link_tags" => true,
    "link_image" => true,
    "link_navigation" => false,
    "split_more" => true,
    "split_point" => "<!--more-->",
    "limit" => false,
    "orderby" => false,
    "order" => false,
    "offset" => false,
    "display_items" => "title,author,date,image,content",
    "template_file" => false,
    "class_author" => "yapl-author",
    "class_title" => "yapl-title",
    "class_date" => "yapl-date",
    "class_date_part" => "yapl-date-",
    "class_content" => "yapl-content",
    "class_categories" => "yapl-categories",
    "class_categories_wrap" => "yapl-categories-wrap",
    "class_tags" => "yapl-tags",
    "class_tags_wrap" => "yapl-tags-wrap",
    "class_wrap" => "yapl-wrap",
    "class_image" => "yapl-image",
    "class_readmore" => "yapl-readmore",
    "class_commentcount" => "yapl-commentcount",
    "class_custom_value" => "yapl-custom-value",
    "class_custom_key" => "yapl-custom-key",
    "class_custom_wrap" => "yapl-custom-wrap",
    "class_outer_wrap" => "yapl-outer-wrap",
    "class_menu_item" => "yapl-menu-item",
    "class_menu" => "yapl-menu",
    "class_menu_wrap" => "yapl-menu-wrap",
    "class_navigation" => "yapl-navigation",
    "class_link_older" => "yapl-older",
    "class_link_earlier" => "yapl-earlier",
    "tag_title" => "h2",
    "tag_date" => "span",
    "tag_date_part" => "span",
    "tag_author" => "span",
    "tag_content" => "div",
    "tag_categories" => "span",
    "tag_categories_wrap" => false,
    "tag_tags" => "span",
    "tag_outer_wrap" => "div",
    "tag_tags_wrap" => false,
    "tag_wrap" => "article",
    "tag_image" => "span",
    "tag_navigation" => "div",
    "tag_readmore" => "span",
    "tag_commentcount" => "span",
    "tag_custom_value" => "span",
    "tag_custom_key" => "span",
    "tag_custom_wrap" => false,
    "tag_menu_item" => false,
    "tag_menu" => false,
    "tag_menu_wrap" => false,
    "sep_categories" => false,
    "sep_tags" => false,
    "sep_custom" => " : ",
    "label_categories" => "Posted in:",
    "label_tags" => "Tagged in:",
    "label_readmore" => "Read more…",
    "label_comment" => "%1 comment",
    "label_comments" => "%1 comments",
    "label_earlier" => "Earlier News",
    "label_older" => "Older News",
    "create_menu" => false,
    "image_size" => false,
    "filter_content" => true,
    "date_format" => false,
    "max_chars" => false,
    "max_words" => false,
    "category_as_class" => false,
    "json" => false,
    "debug" => false,
  ];

  /* Transfer all arguments into the $flags */
  if (is_array($args)) {
    foreach ($args as $argFlag => $argValue) {
      if (trim(strtolower($argValue)) == "false") {
        $flags[$argFlag] = false;
      } else {
        $flags[$argFlag] = trim($argValue);
      }
    }
  }
  $debug = "";
  $displayItems = explode(",", $flags["display_items"]);
  $current_id = get_queried_object_id();
  $debug .= "<pre>Current post ID: " . $current_id . "</pre>";

  /* Build query arguments */
  if ($flags["author"]) {
    if ($flags["author"] == "current") {
      global $current_user;
      wp_get_current_user();
      $query_args["author"] = $current_user->ID;
    } else {
      $authors = explode(",", $flags["author"]);
      foreach ($authors as $author) {
        if (is_numeric($author)) {
          if (isset($query_args["author"])) {
            $query_args["author"] .= "," . $author;
          } else {
            $query_args["author"] = $author;
          }
        } else {
          if (isset($query_args["author_name"])) {
            $query_args["author_name"] .= "," . $author;
          }
          $query_args["author_name"] = $author;
        }
      }
    }
  }
  if ($flags["category"]) {
    $categories = explode(",", $flags["category"]);
    foreach ($categories as $category) {
      if ($category == "_post_") {
        $post_cat = get_the_terms($current_id, "category");
        $debug .= "<pre>Post categories:" . print_r($post_cat, true) . "</pre>";
        if (is_array($post_cat)) {
          foreach ($post_cat as $pcat) {
            $query_args["tax_query"][] = [
              "taxonomy" => $pcat->taxonomy,
              "field" => "slug",
              "terms" => $pcat->slug,
            ];
          }
        }
      } elseif (is_numeric($category)) {
        $query_args["tax_query"][] = [
          "taxonomy" => "category",
          "field" => "id",
          "terms" => $category,
        ];
      } else {
        $query_args["tax_query"][] = [
          "taxonomy" => "category",
          "field" => "slug",
          "terms" => $category,
        ];
      }
    }
    if (
      isset($query_args["tax_query"]) &&
      count($query_args["tax_query"]) > 1
    ) {
      if ($flags["category_join"] == "and") {
        $query_args["tax_query"]["relation"] = "and";
      } else {
        $query_args["tax_query"]["relation"] = "or";
      }
    }
  }
  if ($flags["not_in_category"]) {
    $categories = explode(",", $flags["not_in_category"]);
    foreach ($categories as $category) {
      if (is_numeric($category)) {
        $query_args["tax_query"][] = [
          "taxonomy" => "category",
          "field" => "id",
          "terms" => $category,
          "operator" => "NOT IN",
        ];
      } else {
        $query_args["tax_query"][] = [
          "taxonomy" => "category",
          "field" => "slug",
          "terms" => $category,
          "operator" => "NOT IN",
        ];
      }
    }
    if (count($query_args["tax_query"]) > 1) {
      $query_args["tax_query"]["relation"] = "AND";
    }
  }
  if ($flags["limit"]) {
    $query_args["numberposts"] = $flags["limit"];
  }
  if ($flags["order"]) {
    $query_args["order"] = $flags["order"];
  }
  if ($flags["orderby"]) {
    $query_args["orderby"] = $flags["orderby"];
  }
  if ($flags["offset"]) {
    if ($flags["offset"] == "url") {
      if (isset($_GET["offset"])) {
        $query_args["offset"] = $_GET["offset"];
      }
    } else {
      $query_args["offset"] = $flags["offset"];
    }
  }
  if ($flags["type"] == "subpages") {
    // Special case: list subpages to current page
    $query_args["post_parent"] = $current_id;
    $query_args["post_type"] = get_post_type($current_id);
  } else {
    $query_args["post_type"] = explode(",", $flags["type"]);
  }
  if ($flags["post_id"]) {
    $query_args["post__in"] = explode(",", $flags["post_id"]);
  }
  if ($flags["meta"]) {
    $metas = explode(",", $flags["meta"]);
    foreach ($metas as $meta) {
      $keyvalue = explode("|", $meta);
      if (count($keyvalue) == 3) {
        $query_args["meta_query"][] = [
          "key" => $keyvalue[0],
          "value" => $keyvalue[2],
          "compare" => $keyvalue[1],
        ];
      } elseif (count($keyvalue) == 4) {
        $query_args["meta_query"][] = [
          "key" => $keyvalue[0],
          "value" => [$keyvalue[2], $keyvalue[3]],
          "compare" => $keyvalue[1],
        ];
      } else {
        $keyvalue = explode("=", $meta);
        $query_args["meta_query"][] = [
          "key" => $keyvalue[0],
          "value" => $keyvalue[1],
        ];
      }
    }
  }

  /* Run query */
  if ($flags["template_file"]) {
    $templateFile =
      get_template_directory() . "/" . $flags["template_file"] . ".php";
    if (file_exists($templateFile)) {
      $query = new WP_Query($query_args);
      while ($query->have_posts()) {
        $query->the_post();
        get_template_part($flags["template_file"]);
      }
    }
  } else {
    $html = "";
    $menu = "";
    if ($flags["debug"]) {
      $debug .= "Using query args:<pre>";
      $debug .= print_r($query_args, true);
      $debug .= "</pre>";
    }
    $posts = get_posts($query_args);
    $debug .= "<pre>Posts fetched!" . print_r($posts, true) . "</pre>";

    if ($flags["json"] && $flags["json"] == "raw") {
      $result = ["posts" => $posts];
      return json_encode($result);
    }
    // Exit early if we have no found posts
    if (count($posts) == 0 && !$flags["debug"]) {
      return "";
    }
    foreach ($posts as $post) {
      // Skip if post is the current post
      $is_current = false;
      if ($post->ID == $current_id) {
        // continue;
        $is_current = true;
      }
      $customFields = get_post_custom($post->ID);
      $post_html = "";
      if ($flags["debug"]) {
        $debug .=
          "Customfields: <pre>" . print_r($customFields, true) . "</pre>";
      }

      foreach ($displayItems as $post_item) {
        if ($post_item == "title") {
          $post_html .=
            "<" .
            $flags["tag_title"] .
            ' class="' .
            $flags["class_title"] .
            '">';
          if ($flags["link_title"]) {
            $post_html .= '<a href="' . get_permalink($post->ID) . '">';
          }
          $post_html .= $post->post_title;
          if ($flags["link_title"]) {
            $post_html .= "</a>" . PHP_EOL;
          }
          $post_html .= "</" . $flags["tag_title"] . ">";
        }
        if ($post_item == "image") {
          $post_html .=
            "<" .
            $flags["tag_image"] .
            ' class="' .
            $flags["class_image"] .
            '">';
          if ($flags["link_image"]) {
            $post_html .= '<a href="' . get_permalink($post->ID) . '">';
          }
          if ($flags["image_size"]) {
            $sizes = explode("x", strtolower($flags["image_size"]));
            if (count($sizes) == 2) {
              $post_html .= get_the_post_thumbnail($post->ID, $sizes);
            } else {
              $post_html .= get_the_post_thumbnail($post->ID);
            }
          } else {
            $post_html .= get_the_post_thumbnail($post->ID);
          }
          if ($flags["link_image"]) {
            $post_html .= "</a>";
          }
          $post_html .= "</" . $flags["tag_image"] . ">" . PHP_EOL;
        }
        if ($post_item == "date") {
          $post_html .=
            "<" . $flags["tag_date"] . ' class="' . $flags["class_date"] . '">';
          if ($flags["date_format"] == "split") {
            $post_html .=
              "<" .
              $flags["tag_date_part"] .
              ' class="' .
              $flags["class_date_part"] .
              'year">' .
              get_the_date("Y", $post) .
              "</" .
              $flags["tag_date_part"] .
              ">";
            $post_html .=
              "<" .
              $flags["tag_date_part"] .
              ' class="' .
              $flags["class_date_part"] .
              'month">' .
              get_the_date("M", $post) .
              "</" .
              $flags["tag_date_part"] .
              ">";
            $post_html .=
              "<" .
              $flags["tag_date_part"] .
              ' class="' .
              $flags["class_date_part"] .
              'day">' .
              get_the_date("d", $post) .
              "</" .
              $flags["tag_date_part"] .
              ">";
            $post_html .=
              "<" .
              $flags["tag_date_part"] .
              ' class="' .
              $flags["class_date_part"] .
              'hour">' .
              get_the_date("H", $post) .
              "</" .
              $flags["tag_date_part"] .
              ">";
            $post_html .=
              "<" .
              $flags["tag_date_part"] .
              ' class="' .
              $flags["class_date_part"] .
              'minute">' .
              get_the_date("m", $post) .
              "</" .
              $flags["tag_date_part"] .
              ">";
            $post_html .=
              "<" .
              $flags["tag_date_part"] .
              ' class="' .
              $flags["class_date_part"] .
              'second">' .
              get_the_date("s", $post) .
              "</" .
              $flags["tag_date_part"] .
              ">";
          } elseif ($flags["date_format"]) {
            $post_html .= get_the_date($flags["date_format"], $post);
          } else {
            $post_html .= apply_filters("get_the_date", $post->post_date);
          }
          $post_html .= "</" . $flags["tag_date"] . ">" . PHP_EOL;
        }
        if ($post_item == "author") {
          $post_html .=
            "<" .
            $flags["tag_author"] .
            ' class="' .
            $flags["class_author"] .
            '">' .
            get_the_author_meta("user_nicename", $post->post_author) .
            "</" .
            $flags["tag_author"] .
            ">" .
            PHP_EOL;
        }
        if ($post_item == "content") {
          $post_html .=
            "<" .
            $flags["tag_content"] .
            ' class="' .
            $flags["class_content"] .
            '">' .
            PHP_EOL;
          $content = $post->post_content;
          if ($flags["split_more"]) {
            $splitContent = explode($flags["split_point"], $content);
            $content = $splitContent[0];
          }
          if (
            $flags["max_chars"] &&
            mb_strlen(wp_strip_all_tags($content)) > $flags["max_chars"]
          ) {
            $content = htmlTruncate($content, $flags["max_chars"]) . "…";
          }
          if ($flags["max_words"]) {
            $content = wp_trim_words($content, $flags["max_words"]) . "…";
          }
          if ($flags["filter_content"]) {
            $post_html .= apply_filters("the_content", $content);
          } else {
            $post_html .= $content;
          }
          $post_html .= PHP_EOL . "</" . $flags["tag_content"] . ">" . PHP_EOL;
        }
        if ($post_item == "readmore") {
          $post_html .=
            "<" .
            $flags["tag_readmore"] .
            ' class="' .
            $flags["class_readmore"] .
            '">' .
            '<a href="' .
            get_permalink($post->ID) .
            '">' .
            $flags["label_readmore"] .
            "</a>" .
            "</" .
            $flags["tag_readmore"] .
            ">" .
            PHP_EOL;
        }
        if ($post_item == "comment_count") {
          $post_html .=
            "<" .
            $flags["tag_commentcount"] .
            ' class="' .
            $flags["class_commentcount"] .
            '">';
          $commentCount = get_comments_number($post->ID);
          if ($commentCount == 1) {
            $post_html .= str_replace(
              "%1",
              $commentCount,
              $flags["label_comment"]
            );
          } else {
            $post_html .= str_replace(
              "?",
              $commentCount,
              $flags["label_comments"]
            );
          }

          $post_html .= "</" . $flags["tag_commentcount"] . ">" . PHP_EOL;
        }
        if ($post_item == "categories") {
          $categories = get_the_category($post->ID);
          if ($categories) {
            if ($flags["tag_category_wrap"]) {
              $post_html .=
                "<" .
                $flags["tag_category_wrap"] .
                ' class="' .
                $flags["class_category_wrap"] .
                '">';
            }
            if ($flags["label_categories"]) {
              $post_html .= $flags["label_categories"];
            }
            $categorycount = 1;
            foreach ($categories as $category) {
              if ($flags["sep_categories"] && $categorycount > 0) {
                $post_html .= $flags["sep_categories"];
              }
              $post_html .=
                "<" .
                $flags["tag_categories"] .
                ' class="' .
                $flags["class_categories"] .
                '">';
              if ($flags["link_categories"]) {
                $post_html .=
                  '<a href="' . get_category_link($category->term_id) . '">';
              }
              $post_html .= $category->cat_name;
              if ($flags["link_categories"]) {
                $post_html .= "</a>" . PHP_EOL;
              }
              $categorycount++;
            }
            if ($flags["tag_category_wrap"]) {
              $post_html .= "</" . $flags["tag_category_wrap"] . ">";
            }
          }
        }
        if ($post_item == "tags") {
          $tags = get_the_tags($post->ID);
          if ($tags) {
            if ($flags["tag_tags_wrap"]) {
              $post_html .=
                "<" .
                $flags["tag_tags_wrap"] .
                ' class="' .
                $flags["class_tags_wrap"] .
                '">';
            }
            if ($flags["label_tags"]) {
              $post_html .= $flags["label_tags"];
            }
            $tagcount = 1;
            foreach ($tags as $tag) {
              if ($flags["sep_tags"] && $tagcount > 1) {
                $post_html .= $flags["sep_tags"];
              }
              $post_html .=
                "<" .
                $flags["tag_tags"] .
                ' class="' .
                $flags["class_tags"] .
                " " .
                $tag->slug .
                '">';
              if ($flags["link_tags"]) {
                $post_html .= '<a href="' . get_tag_link($tag->term_id) . '">';
              }
              $post_html .= $tag->name;
              if ($flags["link_tags"]) {
                $post_html .= "</a>" . PHP_EOL;
              }
              $post_html .= "</" . $flags["tag_tags"] . ">";
              $tagcount++;
            }
            if ($flags["tag_tags_wrap"]) {
              $post_html .= "</" . $flags["tag_tags_wrap"] . ">";
            }
          }
        }
        if (array_key_exists($post_item, $customFields)) {
          // Custom field
          $customClass = sanitize_title($customFields);
          if ($flags["tag_custom_wrap"]) {
            $post_html .=
              "<" .
              $flags["tag_custom_wrap"] .
              ' class="' .
              $flags["class_custom_wrap"] .
              " " .
              $customClass .
              '">';
          }
          if ($flags["tag_custom_key"]) {
            $post_html .=
              "<" .
              $flags["tag_custom_key"] .
              ' class="' .
              $flags["class_custom_key"] .
              " " .
              $customClass .
              '">';
            $post_html .= $post_item;
            $post_html .= "</" . $flags["tag_custom_key"] . ">" . PHP_EOL;
            if ($flags["sep_custom"]) {
              $post_html .= $flags["sep_custom"];
            }
          }
          if ($flags["tag_custom_value"]) {
            $post_html .=
              "<" .
              $flags["tag_custom_value"] .
              ' class="' .
              $flags["class_custom_value"] .
              " " .
              $customClass .
              '">';
            $post_html .= apply_filters(
              "yapl_custom_value",
              $customFields[$post_item][0],
              $post_item
            );
            $post_html .= "</" . $flags["tag_custom_value"] . ">" . PHP_EOL;
          }
          if ($flags["tag_custom_wrap"]) {
            $post_html .= "</" . $flags["tag_custom_wrap"] . ">";
          }
        }
      }
      if ($flags["tag_wrap"]) {
        $html .= "<" . $flags["tag_wrap"] . ' class="' . $flags["class_wrap"];
        if ($flags["category_as_class"]) {
          $categories = get_the_category($post->ID);
          foreach ($categories as $category) {
            $html .= " " . $category->slug;
          }
        }
        $html .= '" id="' . $post->post_name . '">';
      }
      $html .= $post_html;
      if ($flags["tag_wrap"]) {
        $html .= "</" . $flags["tag_wrap"] . ">";
      }
      if ($flags["tag_menu_item"]) {
        $menu .=
          "<" .
          $flags["tag_menu_item"] .
          ' class="' .
          $flags["class_menu_item"] .
          '">';
        $menu .=
          '<a href="#' .
          sanitize_title($post->post_title) .
          ">" .
          $post->post_title .
          "</a>";
        $menu .= "</" . $flags["tag_menu_item"] . ">";
      }
    }
    if ($flags["tag_menu"]) {
      $menu =
        "<" .
        $flags["tag_menu"] .
        ' class="' .
        $flags["class_menu"] .
        '">' .
        $menu .
        "</" .
        $flags["tag_menu"] .
        ">" .
        PHP_EOL;
    }
    if ($flags["tag_menu_wrap"]) {
      $menu =
        "<" .
        $flags["tag_menu_wrap"] .
        ' class="' .
        $flags["class_menu_wrap"] .
        '">' .
        $menu .
        "</" .
        $flags["tag_menu_wrap"] .
        ">" .
        PHP_EOL;
    }
    if ($flags["link_navigation"]) {
      $html .=
        "<" .
        $flags["tag_navigation"] .
        ' class="' .
        $flags["class_navigation"] .
        '">';
      global $wp;
      $currentUrl = home_url(add_query_arg([], $wp->request));
      $olderOffset = $flags["limit"];
      if (isset($query_args["offset"]) && $query_args["offset"] > 0) {
        // MAYBE: use post title for next and previous post if limit = 1
        // We already have an offset and needs to go back
        $earlierOffset = $query_args["offset"] - $flags["limit"];
        $olderOffset = $query_args["offset"] + $flags["limit"];
        $html .=
          '<a href="' .
          $currentUrl .
          "?offset=" .
          $earlierOffset .
          '" class="' .
          $flags["class_link_earlier"] .
          '">' .
          $flags["label_earlier"] .
          "</a>";
      }
      // Check if we have any older news...
      if (count($posts) == $flags["limit"]) {
        // Older new available
        $html .=
          '<a href="' .
          $currentUrl .
          "?offset=" .
          $olderOffset .
          '" class="' .
          $flags["class_link_older"] .
          '">' .
          $flags["label_older"] .
          "</a>";
      }
      $html .= "</" . $flags["tag_navigation"] . ">";
    }
    if ($flags["tag_outer_wrap"]) {
      $html =
        "<" .
        $flags["tag_outer_wrap"] .
        ' class="' .
        $flags["class_outer_wrap"] .
        '">' .
        $html .
        "</" .
        $flags["tag_outer_wrap"] .
        ">";
    }
    if ($flags["json"]) {
      $result = ["html" => $menu . PHP_EOL . $html];
      return json_encode($result);
    } elseif ($flags["debug"]) {
      return $debug . $menu . PHP_EOL . $html;
    } else {
      return $menu . PHP_EOL . $html;
    }
  }
}
function htmlTruncate($html, $maxLength)
{
  mb_internal_encoding("UTF-8");
  $printedLength = 0;
  $position = 0;
  $tags = [];
  $out = "";

  while (
    $printedLength < $maxLength &&
    mb_preg_match(
      "{</?([a-z]+)[^>]*>|&#?[a-zA-Z0-9]+;}",
      $html,
      $match,
      PREG_OFFSET_CAPTURE,
      $position
    )
  ) {
    list($tag, $tagPosition) = $match[0];

    // Add text leading up to the tag.
    $str = mb_substr($html, $position, $tagPosition - $position);
    if ($printedLength + mb_strlen($str) > $maxLength) {
      $out .= mb_substr($str, 0, $maxLength - $printedLength);
      $printedLength = $maxLength;
      break;
    }

    $out .= $str;
    $printedLength += mb_strlen($str);

    if ($tag[0] == "&") {
      // Handle the entity.
      $out .= $tag;
      $printedLength++;
    } else {
      // Handle the tag.
      $tagName = $match[1][0];
      if ($tag[1] == "/") {
        // This is a closing tag.

        $openingTag = array_pop($tags);
        assert($openingTag == $tagName); // check that tags are properly nested.

        $out .= $tag;
      } elseif ($tag[mb_strlen($tag) - 2] == "/") {
        // Self-closing tag.
        $out .= $tag;
      } else {
        // Opening tag.
        $out .= $tag;
        $tags[] = $tagName;
      }
    }

    // Continue after the tag.
    $position = $tagPosition + mb_strlen($tag);
  }

  // Print any remaining text.
  if ($printedLength < $maxLength && $position < mb_strlen($html)) {
    $out .= mb_substr($html, $position, $maxLength - $printedLength);
  }

  // Close any open tags.
  while (!empty($tags)) {
    $out .= sprintf("</%s>", array_pop($tags));
  }

  return $out;
}

function mb_preg_match(
  $ps_pattern,
  $ps_subject,
  &$pa_matches,
  $pn_flags = 0,
  $pn_offset = 0,
  $ps_encoding = null
) {
  // WARNING! - All this function does is to correct offsets, nothing else:
  //(code is independent of PREG_PATTER_ORDER / PREG_SET_ORDER)

  if (is_null($ps_encoding)) {
    $ps_encoding = mb_internal_encoding();
  }

  $pn_offset = strlen(mb_substr($ps_subject, 0, $pn_offset, $ps_encoding));
  $ret = preg_match(
    $ps_pattern,
    $ps_subject,
    $pa_matches,
    $pn_flags,
    $pn_offset
  );

  if ($ret && $pn_flags & PREG_OFFSET_CAPTURE) {
    foreach ($pa_matches as &$ha_match) {
      $ha_match[1] = mb_strlen(
        substr($ps_subject, 0, $ha_match[1]),
        $ps_encoding
      );
    }
  }

  return $ret;
}
add_shortcode("yapl", "yapl_shortcode_handler");

?>
