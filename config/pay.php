<?php


return [
    'alipay' => [
        'app_id'         => '2016101000652363',
        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAv/rSQA8j0spHsiXZLscBRyLj6fDqTdQ/0OiGM0NoCKDczysuxZ66xweV1pcdIML916nSdQ5sYmTbxc+8cIPFSbS3f3q+73vQ81aeMAdCh46qX2fy51cLApfEjXJ99cg5JjUJTucuMc9bHYkW7hMs/I03cFGCIUkKMR2ph03hBkFJvjIGeeXAhS8JOnBxei/GGI/AgkH9OrRwlJJeOpRN86zboskPBRkzkjWvumwwRVFFkJ0QqaY2c/z6QyLO/SgkPK/7L8ZUFZ3X6sDhRvLSkz5SbAbP6QEzsCxOSXG3cKvCGJfOM1a6ELxVxlPU6MCJwsBiWDwjYgYxbIIULu55gQIDAQAB',
        'private_key'    => 'MIIEpAIBAAKCAQEAuCmRT4qsSwtMGd+hjohcDBEk7Zh8A9CLNpzUVMFDsUKlZIly9IPQslqzPRQFnZbCaokngCxDDylDXkXjrXvENLeAWL5B8XJ5wuZ5Obi44FLJiQjeQJ7x7cidd4gyArgxaeYxcIhMhHdndqxVu5jh0XhdQNFK9aTtw1YWEXbHpjJIRunegjE4vmqNwqFCJhlq1Zx2kIrGqYhzZeMwruWwFlbAb2J5XEmqK+yp+AP6kNXxme3qCTUrdyh9WNxTflgf4afGMWdQb3yu8umjtujFxGLC6ptMhhVbRj0+SrcJYtfHckxNpaq/OTxPBQU90WhmbIupFbK64ZNgz2d+PKLB3QIDAQABAoIBAC5F64gT7PSyMv3xTfO1WYOCr0ev38FJniEryMI/CVY5y7r2Olcr471cwtqe9EZDgjgonC89b+AYWyAN6YZGqechEHO74m/bdrK9HEqfmnxq8M6o1sdf6KP5m29UXlSGmYvlVhjTdn52Th3ocWZApMTwPGYz8GTGeyNwtvzOXp3oaSz2S0UwY3NL9CrkltxVlzDqlrSLTWzSaCRuqxmpE3ETjQ7H+XikzuO+c1MY5xx6lsqJ/sHWv9Ruhk8p4XS7B84pGg3594YVrMr33U1T0P4Kelv/PIAWWP5ZynvZHJzxcsq3IBuNMfZAf1gIMDLmnuan9jTONSOff+qi+27U0aECgYEA5bXSZXe9Xcn9+gG0ZTNHMGbUc4oFXeElVLSNb7M6SNWgtlRudTlUh8Tx1vIMEj91bH2tIokbju/Bf+8m4f4MBMinxaVaMrlpIuHVju1VHOqhS5gZNcclcTc/U0Leru7DHeRp39bEE64wZ/cAUF6IgIT7QfkFiHG/S2pipPLk4vkCgYEAzT1CUI0H4G6UihIYkUFKqCZ2rgQ8V6oXFXH4r4z2Rb+Nkg1RkczDzEB/XtpvdHIfQKx+ki3qK2Fq1XbDwHdxpEZVlLOZvVbaPt6e47B7uptMlU256F3Kgj7UvRjb6ABKlLBKJAPku//qf/2QI/vLDbrO/n1KPvw7cTFsKwR+qwUCgYEA5N2TEqWKdreZ5E1ufqXod8D8S2Fol3CK0SpTAMeBeq/dv8u/gevs3JjMm2vtuwCe7ckJhy8z1eXq1JxiESgcsWd6QOHOTFuO9MItFIpCEt1ydu7Dr2ELXRG+diZZ19Drdgztr6c7FKCoee0O0wRc3C+iZsjFSnyKK4mDXvTiN4ECgYEAmzU9Zh8RGtdlO3jt3UMsCzdzPqvzWjRF2rDi6RdH0n1GuXGbAxB/7YHFEN1Nthjrq4QG/9L7KK02FpIG/REW/q014QtlMztwNmUycWuwVfFFnA84jRIIqDCNvycCflNiE0mx98R+APJKy1nc3Gl5KDvid7AWKsWiyjoe2/U9OWUCgYA4kUvtT7fw3NsEriCy731eJLlYn4Po3CH13APgHL6MkYKCX9d7o0NTmnxuTpMubbPY+3OMv6Uxlrcq6p57PF4I1BOIFAwfAZ4gLfHUPPZlVuLYMym8TCadOPRB45kk2mlweyxan5P9835kJbib9gEndkCO8MPcjzSmzggC7DRRrg==',
        'log'            => [
            'file' => storage_path('logs/alipay.log'),
        ],
    ],

    'wechat' => [
        'app_id'      => '',
        'mch_id'      => '',
        'key'         => '',
        'cert_client' => '',
        'cert_key'    => '',
        'log'         => [
            'file' => storage_path('logs/wechat_pay.log'),
        ],
    ]
];
