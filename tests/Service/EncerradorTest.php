<?php

namespace Alura\Leilao\Tests\Service;

use Alura\Leilao\Model\Leilao;
use PHPUnit\Framework\TestCase;
use Alura\Leilao\Service\Encerrador;
use Alura\Leilao\Service\EnviadorEmail;
use Alura\Leilao\Dao\Leilao as LeilaoDao;
use PHPUnit\Framework\MockObject\MockObject;


class EncerradorTest extends TestCase
{

    private $encerrador;
    private $enviadorDeEmailMock;
    private $leilaoFiat;
    private $leilaoVariante;

    protected function setUp(): void
    {
        $this->leilaoFiat = new Leilao('Fiat 147 0Km', new \DateTimeImmutable('8 days ago'));
        $this->leilaoVariante = new Leilao('Variante 0Km', new \DateTimeImmutable('10 days ago'));

        // método mais simples, sem costumizar o mock
        $leilaoDao = $this->createMock(LeilaoDao::class);

        // método mais complexo, com costumizar o mock
        // $leilaoDao = $this->getMockBuilder(LeilaoDao::class)
        //     ->disableOriginalConstructor()
        //     ->getMock();
        $leilaoDao->method('recuperarNaoFinalizados')
            ->willReturn(
                [
                    $this->leilaoFiat, 
                    $this->leilaoVariante
                ]
            );
        $leilaoDao->expects($this->exactly(2))
            ->method('atualiza')
            ->withConsecutive(
                [$this->leilaoFiat],
                [$this->leilaoVariante]
            );

        $this->enviadorDeEmailMock = $this->createMock(EnviadorEmail::class);
        $this->encerrador = new Encerrador($leilaoDao, $this->enviadorDeEmailMock);
    }

    public function testLeiloesComMaisDeUmaSemanaDevemSerEncerrados()
    {
        $this->encerrador->encerra();
        $leiloes = [$this->leilaoFiat, $this->leilaoVariante];
        static::assertCount(2, $leiloes);
        static::assertTrue($leiloes[0]->estaFinalizado());
        static::assertTrue($leiloes[1]->estaFinalizado());
    }

    public function testDeveContinuarOProcessoamentoAoEncontrarErroAoEnviarEmail()
    {
        $e = new \DomainException('Erro ao enviar e-mail');
        $this->enviadorDeEmailMock->expects(self::exactly(2))
            ->method('notificaTerminoLeilao')
            ->willThrowException($e);

        $this->encerrador->encerra();
    }
}