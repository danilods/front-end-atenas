<?php

namespace App\Models;

class DivulgacaoRelatorioModel extends \App\Models\appModel {

	static $table_name = 'divulgacao_relatorio';

	public static function buscar($matricula = null, $data_inicial = null, $data_final = null, $tipoOcorrencia = null, $local = null) {

		return parent::custom("SELECT  aerodromo_geral.id, aerodromo_geral.cidade_id, aerodromo_geral.icao, aerodromo_geral.nome , geografia_cidade.nome as cidade, geografia_uf.nome as uf
FROM (
    geografia_cidade INNER JOIN geografia_uf
    ON geografia_cidade.uf_id = geografia_uf.id
)
INNER JOIN aerodromo_geral
ON geografia_cidade.id = aerodromo_geral.cidade_id WHERE aerodromo_geral.icao LIKE '%$term%' or aerodromo_geral.nome LIKE '%$term%' LIMIT 10");

	}

}