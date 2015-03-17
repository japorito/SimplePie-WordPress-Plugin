<!-- This file is used to markup the administration form of the widget. -->
<h2>Latest Episode Widget Configuration</h2>
<label for="feed">RSS Feed Url: </label>
<input type="text" id="<?php echo $feed_input_id; ?>" name="<?php echo $feed_input_name; ?>" value="<?php echo $feedUrl; ?>"><br>

<label for="itunessub">iTunes Subscribe Url (Optional): </label>
<input type="text" id="<?php echo $itunessub_input_id; ?>" name="<?php echo $itunessub_input_name; ?>" value="<?php echo $iTunes; ?>"><br>

<label for="alleps">Url for Full Feed Content (Optional): </label>
<input type="text" id="<?php echo $alleps_input_id; ?>" name="<?php echo $alleps_input_name; ?>" value="<?php echo $allEpisodes; ?>"><br>
