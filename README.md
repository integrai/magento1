# Extensão Integrai para Magento 1
Módulo para integrar sua loja com a Integrai, integrando com diversos parceiros com apenas 1 plugin.

## Requisitos

- [Magento Community](https://magento.com/products/community-edition) 1.7.x, 1.8.x ou 1.9.x.
- [PHP](http://php.net) >= 5.4.x
- Cron

## Instalação

### Manual
1. Baixe a ultima versão [aqui](https://codeload.github.com/integrai/magento1/zip/master)
2. Descompacte o arquivo baixado e copie as pastas para pasta raiz da sua instalação.
3. Limpe o cache no painel Administrativo em `Sistema > Gerenciamento de Cache`, ou limpe a pasta com o comando abaixo:  
```bash
rm -rf var/cache/*
```

### Composer
```
composer require integrai/core
```

## Configuração
1. Acesse o painel administrativo da sua loja
2. Vá em `Sistema > Configuração > Integrai > Configurações`
3. Informe sua **API Key** e sua **Secret Key**, que são informadas [aqui](https://manage.integrai.com.br/settings/account)
4. Salve as configurações
5. Em `Sistema > Configuração > Configuração do cliente > Opções de Nome e Endereço`, altere o valor dos campos:
* `Número de linhas em um endereço de rua` com valor `4`
*  `Exibir Tax/Vat` com valor `Habilitado`
7. Salve as configurações
