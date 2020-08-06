# [aws-sns-to-slack](https://github.com/emiliaanoldz/aws-sns-to-slack)

Script para convertir alerta de Amazon SNS en un mensaje automático para Slack.


# Escenario

Utilizando Amazon SES para el envío masivo de correos electrónicos, me di cuenta que la mejor forma para monitorear los *bounces & complaints* era a través de alertas automáticas a nuestro Slack.

## Crear la alerta

Para utilizar el script, debes crear un bot para utilizar un Webhook. [Mas información.](https://api.slack.com/messaging/webhooks)

## Apuntar SNS a nuestro servidor.

Para que el envío se efectúe, hay que apuntar el SNS a nuestro script.

# Seguridad

Usando el SDK de Amazon para PHP verificamos que la firma del SNS provenga de Amazon.

# Contacto
¿Problemas utilizando el script? [Mandame un mensaje](https://emiliaanoldz.github.io/#contact)
