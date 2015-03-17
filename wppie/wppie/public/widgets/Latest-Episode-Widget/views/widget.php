<!-- This file is used to markup the public-facing widget. -->
<div class="wppie_latest_ep">
  <h2 class="heading">Latest Episode</h2>
  <div class="wppie_latest_audio">
    <h4><?php echo $item->get_title(); ?></h4>
    <small>Posted on <?php echo $item->get_date('j F Y | g:i a'); ?></small>
    <?php
    foreach ($item->get_enclosures() as $enclosure) {
        if (preg_match('/^audio/', $enclosure->get_type())) {
            echo "<p><audio src=\"" . $enclosure->get_link() . "\" controls preload=\"metadata\"></audio></p>";
        }
        else if (preg_match('/^video/', $enclosure->get_type())) {
            echo "<p><video src=\"" . $enclosure->get_link() . "\" controls preload=\"metadata\"></video></p>";
        }
    }
    ?>
  <div class="wppie_latest_alleps"><?php
  if (!empty($instance['alleps'])) {
    echo '<a href="' . $instance['alleps'] . '">See All Episodes</a>';
  }
  ?></div>
  <div class="wppie_latest_subscribe">Subscribe:<?php
    if (!empty($instance['itunessub'])) {
      echo '<a href="' . $instance['itunessub'] . '" title="Subscribe in iTunes">' .
        '<img src="' . $itunes_icon . '">' .
      '</a>';
    }?>
    <a href="<?php echo $instance['feed']; ?>" title="Subscribe by RSS">
      <img src="<?php echo $rss_icon; ?>">
    </a>
    <a href="<?php
        echo 'http://cloud.feedly.com/#subscription%2Ffeed%2F' . urlencode($instance['feed']);
      ?>" title="Subscribe in Feedly">
        <img src="<?php echo $feedly_icon; ?>">
      </a>
  </div>
</div>
