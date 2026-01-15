<div class="card-body">
	<a href="https://www.beneleit.mx/plan-de-pagos-sinergy-de-beneleit/" target="_blank">
		<table class=" mb-4">
			<tr>
				<td>
					<div class="card p-3 me-3"><img src="https://v4.app/assets/img/logo_color.png" width="100"></div>
				</td>
				<td width="100%">
					<p class="m-0">Click aquí para consultar nuestro</p>
					<p class="text-teal fw-bold">Plan de pagos</p>
				</td>
			</tr>
		</table>
	</a>

	<?php
	$videos = [
		[
			"titulo" => "SINERGY Beneleit Pt. 1",
			"url"    => "https://www.youtube.com/embed/yBVAtObTT-o?si=JxUhoWGOXZso0RvM",
		],
		[
			"titulo" => "SINERGY Beneleit Pt. 2",
			"url"    => "https://www.youtube.com/embed/vw4cse3qIbU?si=b_bruYmIND5wuOwV",
		],
		[
			"titulo" => "¿Cómo funcionan los bonos Beneleit?",
			"url"    => "https://www.youtube.com/embed/tRodJJHEZQo?si=bc0ejS29EPuAXHHV",
		],
		[
			"titulo" => "¿Cómo consultar la Guía de la salud?",
			"url"    => "https://www.youtube.com/embed/9lkbouVTAuk?si=5UNV2MXgImzefcmy",
		],
		[
			"titulo" => "¿Cómo obtener material promocional?",
			"url"    => "https://www.youtube.com/embed/_iG-seRn79Y?si=njuowHtKOSGMR6_p",
		],
		[
			"titulo" => "¿Cómo usar el portafolio de opciones de consumo?",
			"url"    => "https://www.youtube.com/embed/P_Mhv05dMcc?si=B3hq4dAIInR15F1G",
		],
		[
			"titulo" => "¿Cómo conectar a mis prospectos a las presentaciones?",
			"url"    => "https://www.youtube.com/embed/g4pBaoaKO8s?si=p7L_VmbfDCmNyQ76",
		],
		[
			"titulo" => "Medios de contacto con la empresa",
			"url"    => "https://www.youtube.com/embed/g88iczcbrlE?si=4M_1yYZW-PHkly31",
		],
		[
			"titulo" => "¿Cómo realizar tu pago con las apps de BBVA y Banco Azteca?",
			"url"    => "https://www.youtube.com/embed/bqEB9-AxGb4?si=TGTodLOrpb5VUzfh",
		]
	];
	?>

	<h4 class="text-center">Información en video:</h4>

	<div class="accordion mb-0" id="accordionExample">
		<?php

		foreach( $videos as $j => $video ){
			
			$k = $j + 1;
			echo "\n
				<div class=\"accordion-item\">
					<h2 class=\"accordion-header\">
						<button class=\"accordion-button small ".( $j ? "collapsed" : "" )." p-2\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#collapse{$k}\" aria-expanded=\"false\" aria-controls=\"collapse{$k}\">
						<strong class=\"me-2\"><i class=\"fab fa-youtube text-red\" style=\"font-size:30px\"></i></strong>{$k}. {$video[ "titulo" ]}
						</button>
					</h2>

					<div id=\"collapse{$k}\" class=\"accordion-collapse collapse ".( $j ? "" : "show" )."\" data-bs-parent=\"#accordionExample\">
						<div class=\"accordion-body text-center\">
							<iframe width=\"100%\" height=\"auto\" style=\"aspect-ratio: 14/7\" src=\"{$video[ "url" ]}\" title=\"YouTube video player\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share\" referrerpolicy=\"strict-origin-when-cross-origin\" allowfullscreen></iframe>
						</div>
					</div>
				</div>";
		}

	?>
	</div>
</div>
