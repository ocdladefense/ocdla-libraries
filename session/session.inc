<?php

namespace Ocdla;

function session_destroy()
{
	// Unset all of the session variables.
	$_SESSION = array();
	// also log the user out of the LOD site by resetting LOD cookies

	// Finally, destroy the session.
	\session_destroy();
	\usleep(1000);
}