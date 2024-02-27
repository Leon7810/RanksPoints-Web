# RanksPoints-Web
Web panel for RanksPoints CS2 plugin
=======
# CS2 RanksPoints Web Panel

## Overview

The CS2 RanksPoints Web Panel is a PHP-based interface I created to work with the CS2 RanksPoints Counter-Strike server plugin. It offers a user-friendly display of player stats such as ranks, playtime, and K/D ratios, making this data easily accessible and visually clear.

## Features

- **Ranking System**: Displays player ranks based on their performance in the game.
- **Player Statistics**: Shows detailed statistics like playtime, K/D ratio, W/L ratio, etc.
- **Image Representation**: Each rank is associated with a specific image for easy identification.
- **Steam ID Integration**: Converts various Steam ID formats to SteamID64 for standardization.
- **Responsive Design**: The interface is designed to be user-friendly and responsive across various devices.

## Dependencies

- **Web Server**: Apache or Nginx recommended.
- **PHP**: Version 7.2 or higher.
- **MySQL Database**: MySQL version 5.6 or higher, or MariaDB.
- **[CS2 RanksPoints Plugin](https://github.com/ABKAM2023/CS2-RanksPoints)**: This web panel is specifically designed to work with the CS2 RanksPoints plugin for Counter-Strike servers. Ensure you have the plugin installed and configured on your game server.

## Installation

1. **Clone the Repository**: Download the project to your local machine or server.
   ```
   git clone https://github.com/Leon7810/RanksPoints-Web.git
   ```
2. **Database Setup**: Import the provided SQL file into your MySQL database to set up the necessary tables.
3. **Configuration**: Edit the `inc/siteConfig.php` and `inc/dbConfig.php` in the PHP scripts to match your site and database settings.
4. **Deployment**: Upload the files to your web server and navigate to the project URL in your browser.

## Usage

After installation, players can view their ranks and statistics by visiting the web page.

## Contributing

Contributions to the CS2 RanksPoints Plugin are welcome. Please feel free to fork the repository, make changes, and submit a pull request. If you find any bugs or have suggestions, please open an issue in the GitHub repository.

## Disclaimer ⚠️⚠️⚠️

This project is a learning endeavor for me and not a professional-grade application. Errors and issues may occur, and any feedback or contributions to improve the project are highly appreciated.

## License

This project is licensed under GPL-3.0 license.

and yes this was written by ai
