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

1. **Download the Project**:
   - Begin by downloading the project files to your local machine or server. Use the following Git command to clone the repository:
     ```
     git clone https://github.com/Leon7810/RanksPoints-Web.git
     ```

2. **Configure the Database Connection**:
   - Open the `inc/config.php` file in a text editor.
   - Edit the configuration settings within this file to establish a connection to your database and configure the site settings as per your requirements.

3. **Deploy to Web Server**:
   - After configuring the settings, upload the project files to your web server.
   - Navigate to your project's URL in a web browser to access the application.

## Usage

After installation, players can view their ranks and statistics by visiting the web page.

## Contributing

Contributions to the CS2 RanksPoints Plugin are welcome. Please feel free to fork the repository, make changes, and submit a pull request. If you find any bugs or have suggestions, please open an issue in the GitHub repository.

## Disclaimer ⚠️⚠️⚠️

This project is a learning endeavor for me and not a professional-grade application. Errors and issues may occur, and any feedback or contributions to improve the project are highly appreciated.

## License

This project is licensed under GPL-3.0 license.
and yes this was written by ai
