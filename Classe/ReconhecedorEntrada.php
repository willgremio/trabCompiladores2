<?php

/**
 * Description of ReconhecedorEntrada
 *
 * @author Willian
 */
class ReconhecedorEntrada {

    private $objTabela;
    private $arrayReconhecedor = array();
    private $tabelaReconhecedor = '';

    public function __construct(Tabela $objTabela) {
        $this->setObjTabela($objTabela);
    }

    private function setObjTabela($objTabela) {
        $this->objTabela = $objTabela;
    }

    private function setTabelaReconhecedor($tabelaReconhecedor) {
        $this->tabelaReconhecedor = $tabelaReconhecedor;
    }

    public function getTabelaReconhecedor() {
        return $this->tabelaReconhecedor;
    }

    public function reconhecer($entrada) {
        $entrada .= '$';
        $this->setValoresNoReconhecedor('$', $entrada, '', 'Empilhar'); // valores iniciais
        $this->gerarDadosDaTabela();
        $this->criarTabela();
    }

    private function criarTabela() {
        $tabelaGerada = $this->criaTabelaReconhecedor();
        $this->setTabelaReconhecedor($tabelaGerada);
    }

    private function gerarDadosDaTabela($iteracao = 1) {

        $ultimoElementoReconhecedor = end($this->arrayReconhecedor);
        $pilha = $ultimoElementoReconhecedor['pilha'];
        $entrada = $ultimoElementoReconhecedor['entrada'];
        $acao = $ultimoElementoReconhecedor['acao'];
        $handle = $ultimoElementoReconhecedor['handle'];


        $ultimoSimboloPilha = substr($pilha, -1);
        $primeiroSimboloEntrada = $entrada[0];

        if ($acao == 'Empilhar') {
            $pilha .= $primeiroSimboloEntrada;
            $entrada = substr($entrada, 1);
            $ultimoSimboloPilha = substr($pilha, -1);
            $primeiroSimboloEntrada = $entrada[0];
        }

        if ($acao == 'Reduzir') {
            $padrao = '/' . $handle . '/';
            $pilha = preg_replace($padrao, '', $pilha);
            $ultimoSimboloPilha = substr($pilha, -1);
            $primeiroSimboloEntrada = $entrada[0];
            $handle = '';
        }

        $precedencia = $this->objTabela->getSimboloPrecedencia($ultimoSimboloPilha, $primeiroSimboloEntrada);
        $acao = $this->getAcaoPrecedencia($precedencia);
        if ($acao == 'Reduzir') {
            $handle = $ultimoSimboloPilha;
        }

        /*if ($iteracao == 6) {
            var_dump($this->arrayReconhecedor);
            var_dump($pilha);
            var_dump($entrada);
            var_dump($handle);
            var_dump($acao);
            exit;
        }*/
        
        $this->setValoresNoReconhecedor($pilha, $entrada, $handle, $acao);

        if ($pilha != '$' || $entrada != '$') {
            $iteracao ++;
            $this->gerarDadosDaTabela($iteracao);
        }
    }

    private function getAcaoPrecedencia($precedencia) {
        if ($precedencia == '<' || $precedencia == '=') {
            return 'Empilhar';
        }

        if ($precedencia == '>') {
            return 'Reduzir';
        }

        return 'Aceita';
    }

    private function setValoresNoReconhecedor($pilha, $entrada, $handle, $acao) {
        $this->arrayReconhecedor[] = [
            'pilha' => $pilha,
            'entrada' => $entrada,
            'handle' => $handle,
            'acao' => $acao
        ];
    }

    public function criaTabelaReconhecedor() {
        $html = '<table cellpadding="10" cellspacing="1" border="1">';
        $html .= '<tr><th>PILHA</th><th>ENTRADA</th><th>HANDLE</th><th>AÇÃO</th></tr>';
        foreach ($this->arrayReconhecedor as $registroReconhecedor) {
            $html .= '<tr align="center">';
            $html .= '<td>' . $registroReconhecedor['pilha'] . '</td>';
            $html .= '<td>' . $registroReconhecedor['entrada'] . '</td>';
            $html .= '<td>' . $registroReconhecedor['handle'] . '</td>';
            $html .= '<td>' . $registroReconhecedor['acao'] . '</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';
        return $html;
    }

}
