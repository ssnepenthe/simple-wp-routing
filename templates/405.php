<?php
/**
 * Shamelessly (and poorly) adapted from the twentytwentytwo 404 template.
 *
 * @todo twentytwentytwo uses Source Serif Pro font family for h2 - worth bundling?
 */
?><!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<meta name="robots" content="noindex,nofollow,noarchive" />
		<title>An Error Occurred: Method Not Allowed</title>
		<style>
			body {
				background-color: #FFF;
				color: #000;
				font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
				font-size: 1.125rem;
				line-height: 1.6;
			}

			div {
				padding-left: max(1.25rem, 5vw);
				padding-right: max(1.25rem, 5vw);
			}

			main {
				margin-top: 1.5rem;
			}

			h2 {
				font-size: clamp(4rem, 40vw, 20rem);
				font-weight: 200;
				line-height: 1;
				margin-bottom: 0;
				margin-top: 0;
				text-align: center;
			}

			p {
				text-align: center;
			}
		</style>
	</head>

	<body>
		<div>
			<main>
				<h2>
					405
				</h2>

				<p>
					The page was requested with an unsupported HTTP method.
				</p>
			</main>
		</div>
	</body>
</html>
