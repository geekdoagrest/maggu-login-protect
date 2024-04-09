# Worais Login Protect

**Contributors:** worais \
**Tags:** wordpress, login, waf, ddos \
**Requires at least:** 5.6 \
**Tested up to:** 6.5 \
**Requires PHP:** 7.2 \
**License:** GPLv3 or later \
**Stable tag:** 1.1.0

Worais Login Protect plugin is an effective tool to enhance the security of your WordPress site, safeguarding it against brute force attacks and ensuring the integrity of user accounts. This plugin monitors and controls failed login attempts, implementing proactive measures to mitigate potential threats.

## Features

- **Intelligent Login Attempts Control:** Detects and logs failed login attempts, analyzing behavioral patterns to identify possible attacks.

- **Automatic Lockout:** After a configurable number of failed login attempts from the same IP address, the plugin automatically blocks access temporarily, significantly reducing the risk of brute force attacks.

- **Flexible Customization:** Easily configure the maximum number of allowed attempts, lockout duration, and customize error messages to inform users about temporary blocks.

- **Detailed Logs:** Maintain a detailed log of all activities related to login attempts, providing valuable insights for security analysis.

- **Captcha** Native integration of a captcha challenge for an additional layer of security. Requires users to prove they are not automated bots before accessing the login page.

## Installation

1. Install this plugin using WordPress' built-in installer
2. Access the **Login Protect** option under **Settings**
3. Follow the instructions to set up and configure

## Support

If you encounter issues or have improvement suggestions, please [open an issue](https://github.com/worais/login-protect/issues).

## Contributions

Contributions are welcome! Feel free to fork the project and submit [pull requests](https://github.com/worais/login-protect/pulls).

## Screenshots
![](https://github.com/worais/login-protect/blob/main/screenshots/1.png?raw=true)
![](https://github.com/worais/login-protect/blob/main/screenshots/2.png?raw=true)
![](https://github.com/worais/login-protect/blob/main/screenshots/3.png?raw=true)

## Running Tests

To execute the unit tests for Login Protect, you can use Docker Compose. Make sure you have Docker and Docker Compose installed on your system.

1. Open a terminal and navigate to the root directory of the repository.

2. Run the following command to start the tests:

   ```bash
   docker-compose run wordpress php vendor/bin/phpunit
   ```