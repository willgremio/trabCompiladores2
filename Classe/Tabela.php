<?php

/**
 * Description of Tabela
 *
 * @author Willian
 */
require_once('Util.php');

class Tabela {

    private $gramatica = array();
    private $simboloInicial = '';
    private $arrayPrimeiros = array();
    private $arrayUltimos = array();
    private $arrayMenorPrecedencia = array();
    private $arrayMaiorPrecedencia = array();
    private $arrayIgualPrecedencia = array();
    private $tabelaGerada = '';

    public function __construct($dadosGramatica, $simboloInicial) {
        $this->setGramatica($dadosGramatica);
        $this->setSimboloInicial($simboloInicial);
    }

    private function setGramatica($gramatica) {
        $this->gramatica = $gramatica;
    }

    public function getGramatica() {
        return $this->gramatica;
    }

    private function setSimboloInicial($simboloInicial) {
        $this->simboloInicial = $simboloInicial;
    }

    public function getSimboloInicial() {
        return $this->simboloInicial;
    }

    public function setTabelaGerada($tabelaGerada) {
        $this->tabelaGerada = $tabelaGerada;
    }

    public function getTabelaGerada() {
        return $this->tabelaGerada;
    }

    public function getArrayAgrupaLinhasColunas() {
        return $this->arrayAgrupaLinhasColunas;
    }

    public function construcaoTabela($variaveisLadoEsquerdo, $variaveisLadoDireito) {
        $gramatica = array_reverse($this->getGramatica());
        var_dump($gramatica);
        foreach ($gramatica as $ladoEsquerdo => $ladoDireito) {
            $producoesLadoDireito = explode('|', $ladoDireito);
            foreach ($producoesLadoDireito as $producaoLadoDireito) {
                $this->setPrimeiros($ladoEsquerdo, $producaoLadoDireito);
                $this->setUltimos($ladoEsquerdo, $producaoLadoDireito);
            }
        }

        $this->setarMenorPrecedencia();
        $this->setarMaiorPrecedencia();
        $this->setarIgualPrecedencia();
        $tabelaGerada = $this->criaTabela($variaveisLadoDireito);
        $this->setTabelaGerada($tabelaGerada);
    }

    private function setPrimeiros($ladoEsquerdo, $producaoLadoDireito) {
        $primeiroSimboloLadoDireito = $producaoLadoDireito[0];
        if (Util::isSimboloTerminal($primeiroSimboloLadoDireito)) { // se é um terminal
            $this->arrayPrimeiros[$ladoEsquerdo][] = $primeiroSimboloLadoDireito;
        } else { // é um NT
            if ($ladoEsquerdo != $primeiroSimboloLadoDireito) {
                $arrayDoNT = $this->arrayPrimeiros[$primeiroSimboloLadoDireito];
                if (isset($this->arrayPrimeiros[$ladoEsquerdo]) && !in_array($arrayDoNT, $this->arrayPrimeiros[$ladoEsquerdo])) {
                    if (empty($this->arrayPrimeiros[$ladoEsquerdo])) {
                        $this->arrayPrimeiros[$ladoEsquerdo] = $arrayDoNT;
                    } else {
                        foreach ($arrayDoNT as $valor) {
                            $this->arrayPrimeiros[$ladoEsquerdo][] = $valor;
                        }
                    }
                }
            }

            if (isset($producaoLadoDireito[1])) {
                $simboloAoLado = $producaoLadoDireito[1];
                $this->arrayPrimeiros[$ladoEsquerdo][] = $simboloAoLado;
            }
        }
    }

    private function setUltimos($ladoEsquerdo, $producaoLadoDireito) {
        $ultimoSimboloLadoDireito = substr($producaoLadoDireito, -1);
        if (Util::isSimboloTerminal($ultimoSimboloLadoDireito)) { // se é um terminal
            $this->arrayUltimos[$ladoEsquerdo][] = $ultimoSimboloLadoDireito;
        } else { // é um NT
            if ($ladoEsquerdo != $ultimoSimboloLadoDireito) {
                $arrayDoNT = $this->arrayUltimos[$ultimoSimboloLadoDireito];
                if (isset($this->arrayUltimos[$ladoEsquerdo]) && !in_array($arrayDoNT, $this->arrayUltimos[$ladoEsquerdo])) {
                    if (empty($this->arrayUltimos[$ladoEsquerdo])) {
                        $this->arrayUltimos[$ladoEsquerdo] = $arrayDoNT;
                    } else {
                        foreach ($arrayDoNT as $valor) {
                            $this->arrayUltimos[$ladoEsquerdo][] = $valor;
                        }
                    }
                }
            }

            $simboloAnterior = substr($producaoLadoDireito, -2, 1);
            if (isset($simboloAnterior) && Util::isSimboloTerminal($simboloAnterior)) {
                $this->arrayUltimos[$ladoEsquerdo][] = $simboloAnterior;
            }
        }
    }

    private function setarMenorPrecedencia() {
        foreach ($this->getGramatica() as $ladoEsquerdo => $ladoDireito) {
            $producoesLadoDireito = explode('|', $ladoDireito);
            foreach ($producoesLadoDireito as $producaoLadoDireito) {
                preg_match('/.[A-Z]/', $producaoLadoDireito, $matches);
                if (!empty($matches)) {
                    $simboloTerminal = $matches[0][0];
                    $simboloNaoTerminal = substr($matches[0], -1);
                    foreach ($this->arrayPrimeiros[$simboloNaoTerminal] as $simTerminal) {
                        $this->arrayMenorPrecedencia[$simboloTerminal][] = $simTerminal;
                    }
                }
            }
        }

        //definindo menor precedencia pro $
        foreach ($this->arrayPrimeiros[$this->simboloInicial] as $simTerminal) {
            $this->arrayMenorPrecedencia['$'][] = $simTerminal;
        }
    }

    private function setarMaiorPrecedencia() {
        foreach ($this->getGramatica() as $ladoEsquerdo => $ladoDireito) {
            $producoesLadoDireito = explode('|', $ladoDireito);
            foreach ($producoesLadoDireito as $producaoLadoDireito) {
                preg_match('/[A-Z]./', $producaoLadoDireito, $matches);
                if (!empty($matches)) {
                    $simboloTerminal = substr($matches[0], -1);
                    $simboloNaoTerminal = $matches[0][0];
                    foreach ($this->arrayUltimos[$simboloNaoTerminal] as $simTerminal) {
                        $this->arrayMaiorPrecedencia[$simTerminal][] = $simboloTerminal;
                    }
                }
            }
        }

        //definindo quem tem maior precedencia do $
        foreach ($this->arrayUltimos[$this->simboloInicial] as $simTerminal) {
            $this->arrayMaiorPrecedencia[$simTerminal][] = '$';
        }
    }

    private function setarIgualPrecedencia() {
        foreach ($this->getGramatica() as $ladoEsquerdo => $ladoDireito) {
            $producoesLadoDireito = explode('|', $ladoDireito);
            foreach ($producoesLadoDireito as $producaoLadoDireito) {
                preg_match('/.[A-Z]./', $producaoLadoDireito, $matches);
                if (!empty($matches)) {
                    $simboloTerminal1 = $matches[0][0];
                    $simboloTerminal2 = substr($matches[0], -1);
                    $this->arrayIgualPrecedencia[$simboloTerminal1] = $simboloTerminal2;
                }
            }
        }
    }

    //gera a tabela
    public function criaTabela($variaveisLadoDireito) {
        $variaveisLadoDireito[] = '$';

        $html = '<table cellpadding="10" cellspacing="1" border="1">';
        $html .= '<tr>';
        $html .= '<th></th>';

        foreach ($variaveisLadoDireito as $variavelTerminal) {
            $html .= '<th>' . $variavelTerminal . '</th>';
        }

        $html .= '</tr>';

        foreach ($variaveisLadoDireito as $variavelTerminal1) {
            $html .= '<tr align="center">';
            $html .= '<td>' . $variavelTerminal1 . '</td>';
            foreach ($variaveisLadoDireito as $variavelTerminal2) {
                $html .= '<td>' . $this->getSimboloPrecedenciaComCor($variavelTerminal1, $variavelTerminal2) . '</td>';
            }

            $html .= '</tr>';
        }

        $html .= '</table>';
        return $html;
    }

    private function getSimboloPrecedenciaComCor($variavelTerminal1, $variavelTerminal2) {
        $simbolo = $this->getSimboloPrecedencia($variavelTerminal1, $variavelTerminal2);
        if ($simbolo == '<') {
            return '<span style="color: red"> < </span>';
        }

        if ($simbolo == '>') {
            return '<span style="color: greenyellow"> > </span>';
        }

        if ($simbolo == '=') {
            return '<span style="color: orange"> = </span>';
        }
        
        if ($simbolo == 'aceita') {
            return '<span style="color: aquamarine">Aceita</span>';
        }
        
        return '';
    }

    public function getSimboloPrecedencia($variavelTerminal1, $variavelTerminal2) {
        if (isset($this->arrayMenorPrecedencia[$variavelTerminal1])) {
            foreach ($this->arrayMenorPrecedencia[$variavelTerminal1] as $variavelTerm) {
                if ($variavelTerminal2 == $variavelTerm) {
                    return '<';
                }
            }
        }

        if (isset($this->arrayMaiorPrecedencia[$variavelTerminal1])) {
            foreach ($this->arrayMaiorPrecedencia[$variavelTerminal1] as $variavelTerm) {
                if ($variavelTerminal2 == $variavelTerm) {
                    return '>';
                }
            }
        }

        if (isset($this->arrayIgualPrecedencia[$variavelTerminal1])) {
            if ($variavelTerminal2 == $this->arrayIgualPrecedencia[$variavelTerminal1]) {
                return '=';
            }
        }

        if ($variavelTerminal1 == '$' && $variavelTerminal2 == '$') {
            return 'aceita';            
        }

        return ''; //branco para erro
    }

}
