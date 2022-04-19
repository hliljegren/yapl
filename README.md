# YAPL

Yet Another Post Lister is a plugin that lets you create a listing of
pages or post via a short code. In its current incarnation this plugin
doesn't include any css so you are responsibible for setting that up. So
currently this plugin is mainly for theme developers (but see the
section about _styling_ below if you still want to use it).

## License

This plugin is licensed under the MIT licence. See the LICENSE file for more info.

## Usage

The basic usage is:

     [yapl]

This will give a listing of the five latests post with the
title,author,date,image and content in that order. On top of that you
can add several attributes, but, hopefully you don't need to. The most
used attributes are:

- **category** (defaults to none) Lets you add one or more categories
  that is used as a filter for your listing if you want to list posts
  from category "foo" you can do that by adding `category = "foo"`.
  You can add more that one category by seprating them with comma like
  `category = "foo,bar"`
  A special case for category is to use `_post_` as a category. all
  categories from the current post will then be added as the filter.
  You can combine `_post_` with other categories like:

  `[yapl category="_post_,cats"]`

  that will list post with any category from the current post **or**
  the category `cats`.

- **display_items** (defaults to "title,author,date,image,content")
  Is a list of the post's items that you want to display. They will be
  outputted in the same order as in the string. E.g. if we use the
  default we will get the post title, author, date, image and content
  in that order. The content shown is by default everything up till
  the `---more---` block.
- **limit** (Defaults to WordPress default) The number of posts you
  want to display. Setting limit to --1 will list every post, but only
  use that if you really know that the number of posts will not be to
  many...
- **class_outer_wrap** The css class used for the outer wrapper (see
  section about _styling_ below)

E.g. `[yapl category="cats" limit="12" display_items="title" class_outer_wrap="cat-list"]` : Will create a list with just the title
for the 12 last posts in category "cats" the outer wrapper will have a
class of "cat-list" that you can use in your styling...

## Note of caution

It is quite possible to create an endless recursion loop using this plugin.
E.g. if you place the shortcode within a post that should list all posts
you will inevitable create a recursion loop. You have been warned!

## Styling

The plugin creates an outer wrapping element (defaults to a "div") with
a default class of "yapl-outer-wrap". Within that outer wrapper every
listed post will wrapped in another html element (defaults to "article")
with a class (defaults to "yapl-wrap"). Within that element all fields
listed in the "display_items" attribute will be listed. Every field is
enclosed in it's own HTML tag so if we have the following short-code:

     [yapl category="cats", display_items="image,title" class_outer_wrap="cat-list" class_wrap="cat"]

You will get the following structure

     <section class"cat-list">
    	  <article class="cat"
    			<span class="yapl-image"><img src="…">
    			<h2 class="yapl-title">
    	  <article class="cat"
    			...

So to style one cat-post you can use the .cat class in your css and to
set up the layout for the full list you can style .cat-list To style
individual fields in your post you can set the class for that part or
you can usually safely start with the wrapper class. If you want the get
to the title within the .cat-list you can use:
`.cat-list .yapl-title { ... }` or even `.cat-list h2`

Every html-tag and class is configurable by adding the corresponding
attribute. The attributes are named tag\__field_ and class\__field_
where _field_ is the post field you want to set up (see full listing
below). E.g. If you want your titles to use an h4 instead of the default
h2 you can add `tag_title="h4"` as an attribute within your short-code.
If you want your content to have a class of "meow-text" you add
`class_content="meow-text"`

## Full attribute list

### Filtering attributes

The filtering attribute are used to filter out the wanted posts.

- **type** (defaults to post) the post type to query. Can be used to
  query custom post type. E.g. `type="books"`. Multiple post types can
  be supplied in a comma-separated list. E.g. `type="post,book,author"`
  There is a special "type" that you can use and that is _subpages_. This will
  create a list of all posts that have the current post as its parent. For this to
  work you need to have a type that is hierarchical like pages.
- **display_items** (defaults to "title,author,date,image,content")
  The fields to display for each post. The fields will be in the same order as
  listed in the attribute. If your posts contains post meta you can add them
  as normal fields, but they will be displayed as `key : value`. You can control
  both the separator char and the key and or value via the `tag_custom_value` and
  `tag_custom_key` (See below).
  If you want an inner wrap for some of your display items you can wrap them in
  `{`and `}`. E.g if you have `display_items="image,{title,exerpt,readmore}"`
  The title, exerpt and readmore elements will be wrapped in an inner wrapper
  element. The tag and class for that element is controlled via `tag_inner_wrap`
  and `class_inner_wrap`.
- **author** (defaults to none) A list of the authors you want to
  display posts from. Can be either author id or author name. the list
  is separated by comma.
- **category** (defaults to none) The category you want your posts to
  have. Can be a comma-separated list if you want to include more than
  one category. e.g. `category="cats, dogs"` will list posts form
  category "cats" _or_ dogs. The category attribute can also use and
  mix category id like `category="3,4,cats"` Will lists posts in
  category with id 3 **or** 4 **or** with slug _cats_
- **category_join** (defaults to "or"). Sets the relation of the categories.
  The default value `or` lists everything belonging to any of the supplied
  categories. If you only want to display posts belonging that have all of the
  categries, you should set `category_join="and"`. Setting the `category_join`
  to anything else than `"and"` will result in using the default value `"or"`.
- **not_in_category** (defaults to none) The opposite of _category_.
  I.e. will list posts that doesn't belong to the given category. If
  more than one category is given the posts shown will not belong to
  any of the given categories.
- **taxonomy** (defaults to false). Lets you change to a custom taxonomy. Needs
  to be used together with *category*. E.g. if you have a custom taxonomy with
  slug `mytaxonomy` that can have a value of `myvalue` you can set up your listing
  via: `[yapl taxonomy="mytaxonomy" category="myvalue" … ]`
- **post_id** (defaults to empty) If you list one or more
  post_id's only those posts will be listed
- **limit** (defaults to WordPress standard) The number of posts you
  want to display. Setting limit to -1 will list every post, but only
  use that if you really know that the number of posts will not be to
  many...
- **orderby** (defaults to the posts date) Can be used to sort on
  another field than the default (see
  [wp_query](https://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters)
  for a full listing)
- **order** (defaults to descending) can be either 'ASC' or 'DESC' for
  ascending or descending
- **offset** (defaults to none) The number of posts to skip before
  listing starts. Offset can have a special value of `url` that will get the
  offset from the a url parameter `offset` instead. See also `link_naviation`
  below
- **link_navigation** (defaults to false). If set to `true` yapl will output
  navigation links below the listing. This will also need the `offset` to be set to url
  for full functionality. But by setting `link_navigation="true" offset="url"` you will
  get a post-navigation where you can navigate to older or newer post by following the
  links in the same way as normal post-navigation.
- **template_file** (Defaults to none) If you really want to go fancy-pancy you can
  add a template_file attribute that should point to a php file. YAPL will set up every
  post and call the get_template_part() function with the supplied file name.
- **meta** (Defaults to none) Can be used to filter posts with specified meta values.
  The `meta` attribute is a comma-separated list where each part can be used in two ways.
  `"metakey=metavalue"` or `metakey|metacompare|metavalue`. If the last version is used you
  can specify how the meta should compare the value. See [wp_query meta](https://developer.wordpress.org/reference/classes/wp_query/#custom-field-post-meta-parameters) for a full description of possible compare
  values. _Note_ values that requires two values need four values. E.g. `meta="price|BETWEEN|20|40"`
  will find all posts with a meta key "price" that is between 20 and 40. It is possible to mix between the
  two different variants. E.g. The following is valid `meta="booktype=paperback,price|BETWEEN|20|40"`

### Possible display_items values

The following can be used as value for the display_items attribute:

- **title** Displays the post title
- **image** Displays the post thumbnail (See the _image_size_ attribute if a specific size
  is needed)
- **date** Displays the post date (See the _date_format_ attribute on how to format the date)
- **author** Displays the author of the post. By default the _tag_author_ is set to 'a' to display
  an anchor to the authors posts page, but by changing the _tag_author_ to something else (like a 'span') will also remove the href attribute.
- **content** Displays the content of the post. The content is by default trucated via the
  read-more block, but, can aslo be truncated via _max_chars_ and _max_words_ attribute. There is also a _filter_content_ attribute (true by default) that applies the normal content filters on
  the content. **Note** To prevent possible infinite loops any shortcodes in the content will **not** be parsed.
- **excerpt** Displays the excerpt for the post. This actually calls the WordPress built-in
  function to generate the excerpt. I.e. If you have no specific excerpt WordPress will generate one from the content
- **readmore** Generates a link to the post with the text from the _label_readmore_ as the link
  text.
- **comment_count** Shows the number of comments for the post. The comment count is displayed via the
  _label_comment_ and _label_comments_ attributes.
- **categories** Displays the post categories. The categories will be prepended with the text in
  the attribute _label_categories_ and each category will be separated with the _sep_categories_
  attribute.
- **tags** Displays all tags associated to the post. The tags will be prepended with the text in
  the attribute _label_tags_ and each tag will be separated with the _sep_tags_ attribute.
- **(custom-field)** Displays a named custom field (post-meta) for the post. I.e. if the post
  has a custom field named _cost_ you can show that by adding _cost_ to the display*items. By default both the key and the value for the custom field is displayed. You can set the attributes \_tag_custom_value* or _tag_custom_key_ to _false_ if you want to disable any of them.

### Class attributes

The class attributes changes the class for the given field.

- **class_title** (defaults to "yapl-title") The class for the title
  field
- **class_date** (defaults to "yapl-date") the class for the date
  field
- **class_date_part** (defaults to "yapl-date-") The class for the
  date part used when splitting date into multiple parts (See
  date-format below) The class have the unit added to the class name.
  I.e. using the default value the year will have a class of
  _yapl-date-year_
- **class_content** (defaults to "yapl-content") The class for the
  content field.
- **class_excerpt** (defaults to "yapl-excerpt") The class for the
  excerpt field
- **class_categories** (defaults to "yapl-categories") the class for
  each category item
- **class_categories_wrap** (defaults to "yapl-categories-wrap") The
  class for the element wrapping all categories)
- **class_tags** (defaults to "yapl-tags") The class for each tag
  item
- **class_tags_wrap** (defaults to "yapl-tags-wrap") the class for
  the element wrapping all tags.
- **class_wrap** (defaults to "yapl-wrap") The class for the post
  wrapper.
- **class_outer_wrap** (defaults to "yapl-outer-wrap") The class for
  the outer wrapper.
  **class_inner_wrap** (defaults to yapl-inner-wrap) The class for the
  inner wrap if used (see *display_items* for more info)
- **class_image** (defaults to "yapl-image") The class for the image
  wrapper
- **class_readmore** (defaults to "yapl-readmore". The class for the
  "read more..." element.
- **class_commentcount** (defaults to "yapl-commentcount") The class
  for the comment count.
- **class_custom_value** (defaults to "yapl-custom-value") The class
  for one custom value.
- **class_custom_key** (defaults to "yapl-custom-key") The class for
  one custom key.
- **class_custom_wrap** (defaults to "yapl-custom-wrap") The class
  for the custom field wrapper. (see _Advanced usage_ below)
- **class_menu_item** (defaults to "yapl-menu-item") The class for
  created menu items
- **class_menu** (defaults to "yapl-menu") The class for the created
  menu
- **class_menu_wrap** (defaults to "yapl-menu-wrap" The class for
  the created menu wrapper

### Tag attributes

The tag attributes can change the default HTML tag used for the
different fields. Setting any wrapping element to false will omit that
wrapper

- **tag_title** (defaults to h2) The tag for the title.
- **tag_date** (defaults to span) The tag for the date.
- **tag_date_part** (defaults to span) The tag for the date_part.
- **tag_author** (defaults to span) The tag for the author.
- **tag_content** (defaults to div) The tag for the content.
- **tag_excerpt** (dafaults to div) The tag for the excerpt.
- **tag_categories** (defaults to span) The tag for the categories.
- **tag_categories_wrap** (defaults to false) The tag for the
  categories_wrap.
- **tag_tags** (defaults to span) The tag for the tags.
- **tag_outer_wrap** (defaults to section) The tag for the outer_wrap.
- **tag_inner_wrap** (defaults to div) the tag used for the inner_wrap.
- **tag_extra_wrap** (defaults to false) the tag used for the extra wrap. If set
  to anything else than false an extra wrapper will be added to enclose all
  content that is outputted. This can be useful for creating CSS-only sliders as
  an eventual menu **and** all listed posts will be enclosed in an element.
- **tag_tags_wrap** (defaults to false) The tag for the tags_wrap.
- **tag_wrap** (defaults to article) The tag for the wrap.
- **tag_image** (defaults to span) The tag for the image.
- **tag_readmore** (defaults to span) The tag for the readmore.
- **tag_commentcount** (defaults to span) The tag for the
  commentcount.
- **tag_custom_value** (defaults to span) The tag for the
  custom_value. If set to false no value will not be outputted.
- **tag_custom_key** (defaults to span) The tag for the custom_key.
  If set to false no value will not be outputted.
- **tag_custom_wrap** (defaults to false) The tag for the
  custom_wrap. Set to anything else than false to get a wrapper
  element around the key/value-pair.
- **tag_menu_item** (defaults to false) The tag for the menu*item.
  (See \_Advanced usage* below)
- **tag_menu** (defaults to false) The tag for the menu.
- **tag_menu_wrap** (defaults to false) The tag for the menu_wrap.

### Link attributes

If link attributes are set to false the given item will not be linked

- **link_title** (defaults to true) If the title should link to the
  post.
- **link_categories** (defaults to true) If the categories should
  link to a category list.
- **link_tags** (defaults to true) If the tags should link to the
  tags list.
- **link_image** (defaults to true) if the image should link to the
  post.

### Separator Attributes

The separator tags are the text that should go between elements of the
same kind. If set to false no text will be inserted. The text is
inserted as is so you can add any valid HTML if there is need.

- **sep_tags** (defaults to false) The text to go between multiple
  tags
- **sep_categories** (defaults to false) the text to go between
  multiple categories
- **sep_custom** (defaults to " : ") the text to go between custom
  key and custom value. This will only be displayed if the custom
  key is displayed. i.e. the tag_custom_key contains an html tag.

### Label Attributes {#labelattributes}

The label attributes are text strings to be displayed as labels before
or after a specific element.

- **label_categories** (defaults to "Posted in:") The label to be
  displayed before categories.
- **label_tags** (defaults to "Tagged in:") The label to be displayed
  before tags.
- **label_readmore** (defaults to "Read more...") The text for the
  readmore link.
- **label_comment** (defaults to "%1 comment") The text to be shown
  to show there is a single comment.
- **label_comments** (defaults to "%1 comments") The text to be shown
  to display there are multiple comments.
- **label_earlier** (defaults to "Earlier News") The text to link to newer
  posts.
- **label_older** (defaults to "Older News") The text to show as the link
  for older posts.

### Content shortening

There are three attributes that are used to determine if the content
part should be shortened in any way. If the content is showed at all it
is by default clipped at the normal `<!—more—>` tag. You can use:

- **split_more** (Defaults to true) will split the content at the
  split-point and thus only show the content before the split-point
- **split_point** (Defaults to `<!--more-->`) the characters where
  the split should occur. You probably never need to set this unless you
  have some special demands.
- **max_chars** (Not used by default) If set to a number the number
  sets the maximum number of characters that should be used from the
  content. If split_more is still true, the shortest of the two will
  be used. I.e. The split will be applied first and after that the
  max_chars will be checked to see if the text needs be shortened
  further.
- **max_words** (Not used by default) If set the content will have a
  maximum length of the number of words set. This is also applied after
  the _split_point_ (and after the _max_chars_).

### Formatting dates via the `date_format` attribute

By default the standard WordPress date is used for dates but you can
change the date by specifying a date format via `date_format` attribute.
The `date_format` can have a normal [wordpress format
string](https://codex.wordpress.org/Formatting_Date_and_Time). It is
also possible to set the `date_format` to "split" as a special case. The
date will then be spliced up with a `<span>` around each time unit.

## Advanced usage

Yapl have the possibility to create a local menu that can navigate to
the listed posts. There are three specific attributes that you can set
to create the menu, `tag_menu_item`, `tag_menu` and
`tag_menu_wrap`. If `tag_menu_item` is set to an html tag it
will be used for wrapping the anchor tags that will lead to the
different local posts. `tag_menu` will wrap the menu and finally if
`tag_menu_wrap` is set it will be used as an outer wrapper. To get
an structure of

     <nav>
    	  <ul>
    			<li><a>

You can use:

     [yapl tag_menu_item="li" tag_menu="ul" tag_menu_wrap="nav"]

**NOTE!** If you don't set the `tag_menu_item` attribute you will never
get any menu items in the output and thus no menu.

### Other attributes

- **image_size** (Not used by default) if set in the format width x
  height the images will be set to the specified size. Otherwise the
  images will be full-sized.
