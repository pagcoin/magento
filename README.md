PagCoin para Magento
=======

Módulo para integrar sistemas de e-commerce desenvolvidos com a ferramenta Magento com o gateway de pagamentos de bitcoin PagCoin

### Instalando o Plugin ###
Coloque os arquivos do modulo na raiz da sua aplicação magento. O código será colocado em app/code/community/PagCoin/BTCPayment, e o arquivo de configuração do plugin será colocado em app/etc/modules

### Configurando o Plugin ###
Após copiar os arquivos para sua aplicação magento, é hora de configurar o módulo. Para isso, realize os seguintes passos:

- Acesse o painel de administração de seu e-commerce, clique em Sistema, e depois em Configuração.
- Procure no menu à esquerda a opção Formas de Pagamento.
- Expanda a aba "PagCoin"
- Informe sua API Key (disponível em https://pagcoin.com/Painel/Api)
- Clique em Salvar


### Configurando a URL de Callback (IPN) ###
Para receber a notificação de pagamentos confirmados e ter a criação automática do Invoice, você deve configurar a URL de Callback. Para isso, realize os seguintes passos:

- Entre no site do PagCoin (http://www.pagcoin.com)
- Acesse o Painel de Controle
- Selecione a opção Configurações de API.
- Preencha o campo URL de Callback (IPN) com o seguinte valor, alterando "enderecoDeSuaLoja" pelo domínio de seu site:
 - http://enderecoDeSuaLoja/BTCPayment/PagCoin/IPN
-  Clique em Salvar
