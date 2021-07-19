<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * format_board
 *
 * @package    format_board
 * @author     Rodrigo Brandão (rodrigobrandao.com.br)
 * @copyright  2017 Rodrigo Brandão
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Formato em blocos';
$string['currentsection'] = 'Este tópico';
$string['editsection'] = 'Editar tópico';
$string['deletesection'] = 'Deletar tópico';
$string['sectionname'] = 'Tópico';
$string['section0name'] = 'Geral';
$string['hidefromothers'] = 'Esconder tópico';
$string['showfromothers'] = 'Mostrar tópico';
$string['page-course-view-topics'] = 'Qualquer página principal curso em formato de tópicos';
$string['page-course-view-topics-x'] = 'Qualquer página do curso no formato de tópicos';
$string['showdefaultsectionname'] = 'Mostrar o nome padrão das seções';
$string['showdefaultsectionname_help'] = 'Se nenhum nome for definido para a seção nada será mostrado.<br>
Por definição, uma seção sem nome é exibida como <strong> Tópico N</strong>.';
$string['yes'] = 'Sim';
$string['no'] = 'Não';
$string['sectionlayout'] = 'Estilo das seções';
$string['sectionlayout_help'] = 'Escolha o tema que as secções devem ser exibidas:<br>
<strong>Limpo</strong><br>
É um tema que irá exibir as seções sem adicionar bordas ou cores. As seções terão uma margem de 40px.<br>
<strong>Blocos</strong><br>
É um tema que irá exibir as seções dentro de blocos com título e bordas estilizadas.
O resumo da seção têm 0px do espaçamento em relação à borda, isso permite ser usado imagens para ilustrar a parte superior do bloco.';
$string['none'] = 'Limpo';
$string['blocks'] = 'Blocos';
$string['widthcol'] = 'Largura do agrupamento';
$string['widthcol_help'] = 'O agrupamento de seções se tornará uma coluna se a largura for definida com resultado da soma entre 99%/100%.
<i> Exemplo: Definir largura Grupo 1 = 33%, largura Grupo 2 = 33% e largura Grupo 3 = 33%, o resultado será uma disposição com 3 colunas </i>.';
$string['numsectionscol'] = 'Quantidade de seções no agrupamento';
$string['numsectionscol_help'] = 'Defina o número de seções que estarão dentro do grupo.<br> As largura da seções será herdada do grupo.';
$string['unlimited'] = 'Ilimitado';
$string['color'] = 'Cor';
$string['color_help'] = 'Defina uma cor em hexadecimal.<i>Exemplo: #fab747 </i><br>Se quiser usar a cor padrão deixe em branco.';
