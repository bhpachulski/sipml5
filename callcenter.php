<!DOCTYPE html>
<html>
<head>
	<title></title>

	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

	<style type="text/css">

		.celular {
			text-decoration: underline;
			cursor: pointer;
		}

	</style>
</head>
<body>

	<div class="row">

		<div class="col-lg-6">

			<table class="table table-hover">
				<thead class="thead-inverse">
					<tr>
						<th>#</th>
						<th>Código</th>
						<th>Nome</th>
						<th>E-Mail</th>
						<th>Celular</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th scope="row">1</th>
						<td>12</td>
						<td>Bruno Henrique Pachulski Camara</td>
						<td class="email">bhpachulski@gmail.com</td>
						<td class="celular">(44) 9 9927-5986</td>
					</tr>
					<tr>
						<th scope="row">2</th>
						<td>24</td>
						<td>Tiago José Marciano</td>
						<td class="email">tjmarciano@gmail.com</td>
						<td class="celular">(44) 9 9988-1235</td>
					</tr>
				</tbody>
			</table>

		</div>

		<div class="col-lg-4" style="border: 1px solid #000;">
			
			<button type="button" class="btn btn-primary" data-toggle="button" aria-pressed="false" autocomplete="off">
  Single toggle
</button>


		</div>

	</div>

</body>

<script type="text/javascript">
	
	$('.celular').click(function () {
		alert('Teste');
	});

</script>

</html>