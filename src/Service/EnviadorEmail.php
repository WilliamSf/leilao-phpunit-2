<?php

namespace Alura\Leilao\Service;

use Alura\Leilao\Model\Leilao;

class EnviadorEmail
{
    public function notificaTerminoLeilao(Leilao $leilao)
    {
        $sucesso = mail(
            'williaamsf@gmail.com',
            'Leilão finalizado',
            "Leilão para {$leilao->recuperarDescricao()} finalizado."
        );

        if (!$sucesso) {
            throw new \DomainException('Erro ao enviar e-mail');
        }
    }
}