<?php
  $title = "DIY Raspberry Pi Checker";
  $servers = array(
    'raspberrypi1.com' => array( 'ip' => 'raspberrypi1.com', 'description' => 'Example description for raspberrypi1.com', 'port' => 22),
    'raspberrypi2.com' => array( 'ip' => 'raspberrypi2.com', 'description' => 'Example description for raspberrypi2.com', 'port' => 22),
    'raspberrypi3.com' => array( 'ip' => 'raspberrypi3.com', 'description' => 'Example description for raspberrypi3.com', 'port' => 22)
  );

	if (isset($_GET['host'])) {
	    $host = $_GET['host'];
	    if (isset($servers[$host])) {
	        header('Content-Type: application/json');

	        $return = array(
	            'status' => test($servers[$host])
	        );

	        echo json_encode($return);
	        exit;
	    } else {
	        header("HTTP/1.1 404 Not Found");
	    }
	}

	$names = array();
	foreach ($servers as $name => $info) {
	    $names[$name] = md5($name);
	}


	?>
<!doctype html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title><?php echo $title; ?></title>
    <link rel="icon" href="favicon.ico">
		<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootswatch/2.3.2/cosmo/bootstrap.min.css">
		<link rel="stylesheet" href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.min.css">
		<style type="text/css">
			body {
				background-color: #212529;
				color: white;
				text-align: center;
			}
			h1, h2, h3, a {
				color: white;
			}
			.manage {
				color: black !important;
			}
		</style>
	</head>
	<body>
		<div class="container">
			<h1><?php echo $title; ?></h1>
			<br>
			<table class="table">
				<tr>
					<td style="background-color: rgb(3, 117, 180)">BLUE => CONNECTING</td>
					<td style="background-color: #3fb618">GREEN => CONNECTED</td>
					<td style="background-color: #ff0039">RED => NO RESPONSE</td>
				</tr>
			</table>
			<table class="table">
				<thead>
					<tr>
						<th style="background-color: white;"></th>
						<th style="background-color: white; color: black;">DNS Name</th>
						<th style="background-color: white; color: black;">Location/Description</th>
						<th style="background-color: white; color: black;">Management</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($servers as $name => $server): ?>
					<tr style="background-color: rgb(3, 117, 180);" id="<?php echo md5($name); ?>">
						<td><i class="loading icon-spinner icon-spin icon-large"></i></td>
						<td class="name"><?php echo $name; ?></td>
						<td><?php echo $server['description']; ?></td>
						<td><?php echo '<u><a class="manage" href="ssh://pi@'. $name .'">Manage</a></u>';?></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
		<script type="text/javascript">
			function test(host, hash) {
			    // Fork it
			    var request;
			    // fire off the request to /form.php
			    request = $.ajax({
			        url: "<?php echo basename(__FILE__); ?>",
			        type: "get",
			        data: {
			            host: host
			        },
			        beforeSend: function () {
			            $('#' + hash).children().children().css({'visibility': 'visible'});
			        }
			    });

			    // callback handler that will be called on success
			    request.done(function (response, textStatus, jqXHR) {
			        var status = response.status;
			        var statusClass;
			        if (status) {
			            statusClass = 'success';
			        } else {
			            statusClass = 'error';
			        }
			        $('#' + hash).removeClass('success error').addClass(statusClass);
			    });
			    // callback handler that will be called on failure
			    request.fail(function (jqXHR, textStatus, errorThrown) {
			        // log the error to the console
			        console.error(
			            "The following error occured: " +
			                textStatus, errorThrown
			        );
			    });
			    request.always(function () {
			        $('#' + hash).find(".loading").css({'visibility': 'hidden'});
			    })
			}

			$(document).ready(function () {
				setTimeout(function()
				{
				    var servers = <?php echo json_encode($names); ?>;
				    var server, hash;
				    for (var key in servers) {
				        server = key;
				        hash = servers[key];
				        test(server, hash);
				        (function loop(server, hash) {
				            setTimeout(function () {
				                test(server, hash);

				                loop(server, hash);
				            }, 35000);
				        })(server, hash);
				    }
				},
				3000);
			});
		</script>
	</body>
</html>
<?php
function test($server) {
    $socket = @fsockopen($server['ip'], $server['port'], $errorNo, $errorStr, 3);
    if ($errorNo == 0) {
        return true;
    } else {
        return false;
    }
}
?>
