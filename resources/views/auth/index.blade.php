<!doctype html>
<html lang="en" dir="ltr">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Musas - Xinergia</title>

	<!-- Favicon -->
	<!-- <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}" /> -->
	<link rel="icon" href="{{ asset('assets/icon/logo.svg') }}" type="image/svg+xml" />

	<!-- Library / Plugin Css Build -->
	<link rel="stylesheet" href="{{ asset('assets/css/core/libs.min.css') }}" />

	<!-- Hope Ui Design System Css -->
	<link rel="stylesheet" href="{{ asset('assets/css/hope-ui.min.css?v=2.0.0') }}" />

	<!-- Custom Css -->
	<link rel="stylesheet" href="{{ asset('assets/css/custom.min.css?v=2.0.0') }}" />

	<!-- Dark Css -->
	<link rel="stylesheet" href="{{ asset('assets/css/dark.min.css') }}" />

	<!-- Customizer Css -->
	<link rel="stylesheet" href="{{ asset('assets/css/customizer.min.css') }}" />

	<!-- RTL Css -->
	<link rel="stylesheet" href="{{ asset('assets/css/rtl.min.css') }}" />

	<style>
		/* Estilos para el contenedor principal */
		.login-content {
			display: flex;
			flex-direction: column;
			justify-content: center;
			align-items: center;
			height: 100vh;
			/* Ocupa toda la altura de la pantalla */
		}

		/* Mover el formulario de Iniciar Sesión hacia abajo */
		.auth-card {
			margin-top: 100px;
			/* Aumenté este valor para mover el formulario más abajo */
			margin-bottom: 40px;
			/* Espacio entre el formulario y el bloque de Xpande */
		}

		/* Mover el bloque de "Elaborado por" más abajo */
		.xpande-section {
			text-align: center;
			margin-top: 80px;
			/* Reduje este valor para que no esté demasiado abajo */
		}
	</style>
</head>

<body class=" " data-bs-spy="scroll" data-bs-target="#elements-section" data-bs-offset="0" tabindex="0">
	<!-- loader Start -->
	<div id="loading">
		<div class="loader simple-loader">
			<div class="loader-body"></div>
		</div>
	</div>
	<!-- loader END -->

	<div class="wrapper">
		<section class="login-content">
			<div class="row m-0 align-items-center bg-white vh-100">
				<div class="col-md-6">
					<div class="row justify-content-center">
						<div class="col-md-10">
							<div class="card card-transparent shadow-none d-flex justify-content-center mb-0 auth-card">
								<div class="card-body">
									<a href="" class="navbar-brand d-flex align-items-center mb-3">
									</a>
									<h2 class="mb-2 text-center">Iniciar Sesión</h2>
									<p class="text-center">Ingresa con tu cuenta</p>
									<form method="POST" action="{{ route('login') }}">
										@csrf <!-- Token de seguridad -->
										<div class="row">
											<div class="col-lg-12">
												<div class="form-group">
													<label for="email" class="form-label">Usuario</label>
													<input type="text" class="form-control" id="email" name="email" aria-describedby="email" placeholder=" " required>
												</div>
											</div>
											<div class="col-lg-12">
												<div class="form-group position-relative">
													<label for="password" class="form-label">Contraseña</label>
													<div class="position-relative">
														<input type="password" class="form-control pe-5" id="password" name="password" required>
														<span class="position-absolute end-0 top-50 translate-middle-y me-3 cursor-pointer" id="togglePassword" style="cursor: pointer;">
															<svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
																<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
																<circle cx="12" cy="12" r="3"></circle>
															</svg>
														</span>
													</div>
													@error('email')
													<small style="color: red;">{{ $message }}</small> <!-- Mensaje de error para el nombre -->
													@enderror
												</div>
											</div>
										</div>
										<div class="d-flex justify-content-center">
											<button type="submit" class="btn btn-primary">Iniciar Sesión</button>
										</div>
									</form>
								</div>
							</div>
						</div>

						<!-- Bloque de Xpande -->
						<div class="xpande-section">
							<p class="mb-1">Elaborado por</p>
							<img src="{{ asset('assets/icon/xinergia.jpeg') }}" alt="Icono 1" class="icon-custom mx-1" width="115" height="20">

							<div class="mt-2 d-flex justify-content-center align-items-center">
								<p class="mb-0 me-2">Somos parte de</p>
								<img src="{{ asset('assets/icon/xpandecorp.jpeg') }}" alt="Icono 2" class="icon-custom mx-1" width="100" height="20">
							</div>
						</div>

					</div>
					<div class="sign-bg">
						<svg width="280" height="230" viewBox="0 0 431 398" fill="none" xmlns="http://www.w3.org/2000/svg">
							<g opacity="0.05">
								<rect x="-157.085" y="193.773" width="543" height="77.5714" rx="38.7857" transform="rotate(-45 -157.085 193.773)" fill="#3B8AFF" />
								<rect x="7.46875" y="358.327" width="543" height="77.5714" rx="38.7857" transform="rotate(-45 7.46875 358.327)" fill="#3B8AFF" />
								<rect x="61.9355" y="138.545" width="310.286" height="77.5714" rx="38.7857" transform="rotate(45 61.9355 138.545)" fill="#3B8AFF" />
								<rect x="62.3154" y="-190.173" width="543" height="77.5714" rx="38.7857" transform="rotate(45 62.3154 -190.173)" fill="#3B8AFF" />
							</g>
						</svg>
					</div>
				</div>
				<div class="col-md-6 d-md-block d-none bg-primary p-0 mt-n1 vh-100 overflow-hidden">
					<img src="{{ asset('assets/images/auth/01.png') }}" class="img-fluid gradient-main animated-scaleX" alt="images">
				</div>
			</div>
		</section>
	</div>

	<!-- Library Bundle Script -->
	<script src="{{ asset('assets/js/core/libs.min.js') }}"></script>

	<!-- External Library Bundle Script -->
	<script src="{{ asset('assets/js/core/external.min.js') }}"></script>

	<!-- Widgetchart Script -->
	<script src="{{ asset('assets/js/charts/widgetcharts.js') }}"></script>

	<!-- mapchart Script -->
	<script src="{{ asset('assets/js/charts/vectore-chart.js') }}"></script>
	<script src="{{ asset('assets/js/charts/dashboard.js') }}"></script>

	<!-- fslightbox Script -->
	<script src="{{ asset('assets/js/plugins/fslightbox.js') }}"></script>

	<!-- Settings Script -->
	<script src="{{ asset('assets/js/plugins/setting.js') }}"></script>

	<!-- Slider-tab Script -->
	<script src="{{ asset('assets/js/plugins/slider-tabs.js') }}"></script>

	<!-- Form Wizard Script -->
	<script src="{{ asset('assets/js/plugins/form-wizard.js') }}"></script>

	<!-- AOS Animation Plugin-->

	<!-- App Script -->
	<script src="{{ asset('assets/js/hope-ui.js') }}" defer></script>

	<script>
		document.getElementById("togglePassword").addEventListener("click", function() {
			var passwordInput = document.getElementById("password");
			var eyeIcon = document.getElementById("eyeIcon");

			if (passwordInput.type === "password") {
				passwordInput.type = "text";
				eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><path d="M12 15l3-3-3-3-3 3 3 3z"></path>';
			} else {
				passwordInput.type = "password";
				eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
			}
		});
	</script>

</body>

</html>