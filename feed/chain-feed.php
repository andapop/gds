<?php
/*
* RSS2 Feed Template for displaying RSS2 Chain feed
*/

function gds_rss_date( $timestamp = null ) {
  $timestamp = ($timestamp==null) ? time() : $timestamp;
  echo date(DATE_RSS, $timestamp);
}

function gds_rss_text_limit($string, $length, $replacer = '...') { 
  $string = strip_tags($string);
  if(strlen($string) > $length) 
    return (preg_match('/^(.*)\W.*$/', substr($string, 0, $length+1), $matches) ? $matches[1] : substr($string, 0, $length)) . $replacer;   
  return $string; 
}

$chain_id = get_query_var('chain_id');
$chain_link = site_url('chains/id/').$chain_id;
// get the wristbands in inverse cronological order
$wristbands = gds_get_wristbands_for_chain_cronological_desc($chain_id);


header('Content-Disposition: attachment; filename="chain_'.$chain_id.'_feed"');
header('Content-Type: application/rss+xml; charset=' . get_option('blog_charset'));

echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'; ?>

<rss version="2.0"
  xmlns:content="http://purl.org/rss/1.0/modules/content/"
  xmlns:dc="http://purl.org/dc/elements/1.1/"
  xmlns:atom="http://www.w3.org/2005/Atom"
  xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
  >

  <channel>
    <title>New addition to Chain <?php echo $chain_id?></title>
    <atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
    <link><?php echo $chain_link;  ?></link>
    <description><?php bloginfo_rss("description") ?></description>
    <lastBuildDate><?php gds_rss_date( strtotime($wristbands[0]->date_claimed) ); ?></lastBuildDate>
    
    <sy:updatePeriod><?php echo apply_filters( 'rss_update_period', 'hourly' ); ?></sy:updatePeriod>
    <sy:updateFrequency><?php echo apply_filters( 'rss_update_frequency', '1' ); ?></sy:updateFrequency>

    <generator>http://wordpress.org/?v=<?php echo get_bloginfo('version');?></generator> ?>

  <?php 
    foreach ($wristbands as $wristband) { 
      
      $name =  gds_user_pretty_name($wristband->user_id);
      $content = '<h3>Good deed</h3>: <p>'.$wristband->story1.'</p> | <h3>What I Want to Share</h3>: <p>'.$wristband->story2.'</p>';
      
      if( $wristband->wb_number == '1' ){
            $title_phrase =' has started the chain';
          }
          else {
            $title_phrase =' has added a new wristband';
          } ?>
      <item>
        <title><?php echo $name;  echo $title_phrase;?> on <?php echo $wristband->date_claimed?></title>
        <link><?php echo $chain_link; ?></link>
        <description><?php echo '<![CDATA['.gds_rss_text_limit($content, 500).'<br/><br/><a href="'.$chain_link.'">See the whole chain</a>'.']]>';  ?></description>
        <pubDate><?php gds_rss_date( strtotime($wristband->date_claimed) ); ?></pubDate>
        <guid><?php echo $chain_link.'#wristband-'.$wristband->wb_number; ?></guid>
      </item>
      <?php
    } ?>
  </channel>
</rss>