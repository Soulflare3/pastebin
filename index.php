<?php
	


	$page = "Pastebin";
	
	include "includes/common.php";
	include "includes/page/header.php";
	
	$form_token = uniqid();
    $_SESSION['user_token'] = $form_token;
	
	$alter = (int)$_GET[ "alter" ];
	$isAlteration = ( $alter && ($alter>0) );
	$orig = array();
	
	if( !empty( $alter ) )
	{
		$orig = $db->SelectFirst( "snippets", "id = '$alter'" );
	}
	
	// Remember my name, use email if we haven't pasted before
	$remembered_name = "";
	
	if( !empty( $_SESSION["user_login"] ) ) // we're logged in
	{
		$last_snippet = $db->SelectFirst( "snippets", "shemail = '" . $_SESSION["user_login"] . "'", "nname, language", "ORDER BY id DESC LIMIT 1" );
	
		if( !empty($last_snippet["nname"]) ) // we've been here before, use the last name we used to 
		{
			$remembered_name = $last_snippet["nname"];
		}
		else // use the first bit of our email address
		{
			if( strstr( $_SESSION["user_login"], "steamcommunity.com" ) != FALSE )
			{
				if( !isset( $_SESSION["steam_name"] ) )
				{
					$steam_split = explode( "/", $_SESSION["user_login"] );
					$steamid = $steam_split[count($steam_split)-1];
					
					$profile = simplexml_load_file( 'http://steamcommunity.com/profiles/' . $steamid . '/?xml=1' );
					$_SESSION["steam_name"] = (string)$profile->steamID;
				}
				
				$remembered_name = $_SESSION["steam_name"];
			}
			else
			{
				$email_split = explode( "@", $_SESSION["user_login"] );
				$remembered_name = $email_split[0];
			}
		}
	}
?>

			<h2><?php if($isAlteration ) echo 'Alter Code'; else echo 'Paste Code'; ?></h2>
            
            <p>
				<?php
					if( $isAlteration )
						echo 'You are making an alteration to the code ' . ( empty($orig["sname"]) ? '<em>Untitled</em>' : htmlentities($orig["sname"]) ) . ' by ' . ( empty($orig["nname"]) ? 'Anonymous Coward' : htmlentities($orig["nname"]) ) . '.' ;
					else
						echo 'Use this to create a new paste, only the code field is required, the rest can be left blank.';
				?>
			</p>
			
			<section id="pastearea">
				<form action="paste.php" method="post">
					<?php if( !empty( $alter ) ) { echo '<input type="hidden" name="alter" value="' . $alter . '" />'; } ?>
					<input type="hidden" name="user_token" value="<?php echo  $_SESSION['user_token'];  ?>" />
					<input type="hidden" name="shemail" value="<?php echo  $_SESSION['user_login'];  ?>" />
					<label for="nname">Name:</label>
					<input type="text" name="nname" id="nname" size="45" <?php if(!empty($remembered_name)) {echo 'value="' . htmlentities($remembered_name) . '"';} ?>/><br />
					
					<label for="sname">Script Name:</label>
					<input type="text" name="sname" id="sname"<?php if( !empty( $alter ) ) { echo ' value="Alteration of ' . htmlentities( $orig["sname"] ) . '"'; } ?> size="45" /><br />
					
					<label for="code">Code:</label>
					<textarea rows="25" cols="90" name="code" id="code"><?php if( !empty( $alter ) ) { echo htmlentities( $orig["code"] ); } ?></textarea><br />
                    
                    <label for="lang">Language:</label>
                    <select name="lang" id="lang">
<?php

	$top_langs = $db->QueryArray( "SELECT language FROM snippets GROUP BY language ORDER BY COUNT(*) DESC LIMIT 10" );
	
	foreach( $top_langs as $k => $v )
	{
		echo '			<option value="' . $v["language"] . '">' . htmlentities( $langs["names"][$v["language"]] ) . '</option>' . "\r\n";			
	}

?>
                        <option value="glua">-</option>
<?php
	foreach( $langs["langs"] as $k => $v )
	{
		echo '			<option value="' . $v . '">' . htmlentities( $langs["names"][$v] ) . '</option>' . "\r\n";	
	}
?>
                    </select><br />
					
					<label for="keepfor">Keep For:</label>
					<select name="keepfor" id="keepfor">
						<option value="-1">Forever</option>
						<option value="-1">-</option>
						<option value="12">12 hours</option>
						<option value="24">1 day</option>
						<option value="168">1 week</option>
						<option value="672">4 weeks</option>
					</select><br />

	  <input name="website" type="hidden" id="website" />
         <input name="email" type="text" id="email" style="display:none" value=""/>
					<input type="submit" id="submitbox" name="paste" value="Paste Code" />

				</form>
			</section>
		
<?php include "includes/page/footer.php"; ?>
