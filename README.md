# TFM-Ciber-CEU-Jose-Torre

Título: Laboratorio Red Team con escenarios de ataque reproducibles
Descripción: Diseño de un laboratorio completo de Red Team que se puede desplegar fácilmente desde cero. Incluye varios escenarios de ataque guiados, documentados y repetibles para practicar técnicas reales de Red Team


[OWASP Top 10 2025 riegos mas crítcos](https://owasp.org/Top10/2025/0x00_2025-Introduction/)

| #   | Vulnerabilidad                     | Descripción breve PYME     | Ejemplo realista                                                                  |
| --- | ---------------------------------- | -------------------------- | --------------------------------------------------------------------------------- |
| A01 | Broken Access Control              | Acceso no autorizado       | Admin ve clientes de otros, bypass ID usuario [owasp.org/Top10/2025/A01]          |
| A02 | Security Misconfiguration          | Configs inseguras          | PHP error reporting ON, headers faltan, .git expuesto [owasp.org/Top10/2025/A02]  |
| A03 | Software Supply Chain Failures     | Dependencias rotas         | npm audit 50+ vulns, composer sin updates [owasp.org/Top10/2025/A03]              |
| A04 | Cryptographic Failures             | Cifrado débil              | Contraseñas MD5, cookies sin secure flag [owasp.org/Top10/2025/A04]               |
| A05 | Injection                          | SQLi, XSS, command inj     | Form login sin prepared statements [owasp.org/Top10/2025/A05]                     |
| A06 | Insecure Design                    | Diseño sin threat modeling | Reset pass sin rate limit, flujo auth débil [owasp.org/Top10/2025/A06]            |
| A07 | Authentication Failures            | Login débil                | Sesiones eternas, password reuse [owasp.org/Top10/2025/A07]                       |
| A08 | Software/Data Integrity Failures   | Integridad rota            | Deserialization insegura PHP, updates sin verificación [owasp.org/Top10/2025/A08] |
| A09 | Security Logging Failures          | Sin logs/alertas           | No trackea login fails ni SQLi attempts [owasp.org/Top10/2025/A09]                |
| A10 | Mishandling Exceptional Conditions | Errores revelan info       | Stack traces completos en frontend [owasp.org/Top10/2025/A10]                     |