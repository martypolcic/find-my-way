Flight Information Website

This project is a modern, easy-to-navigate website designed to provide comprehensive information on flights from multiple sources. It features sections for hot destinations, the cheapest flights, and airline statistics. The backend is built with Laravel, using Docker for environment setup, specifically Laravel Sail, while the frontend is developed using React.

Table of Contents

Features

Tech Stack

Prerequisites

Installation

Usage

Contributing

License

Features

Hot Destinations: Explore popular travel destinations.

Cheapest Flights: Find the most affordable flights.

Airline Statistics: Get insights into airline performance and trends.

Modern Design: User-friendly and visually appealing interface.

Tech Stack

Backend: Laravel (PHP) with Docker (Laravel Sail)

Frontend: React

Database: MySQL

Prerequisites

Docker and Docker Compose

Node.js and npm/yarn

Git

Installation

Backend Setup (Laravel)

Clone the repository:

git clone https://github.com/yourusername/flight-info-website.git
cd flight-info-website

Start Docker containers using Laravel Sail:

./vendor/bin/sail up

Run database migrations:

./vendor/bin/sail artisan migrate

Frontend Setup (React)

Navigate to the frontend directory:

cd frontend

Install dependencies:

npm install
# or
yarn install

Start the React development server:

npm start
# or
yarn start

Usage

Once the backend and frontend are set up and running, you can access the website at http://localhost:3000.

Contributing

Contributions are welcome! Please fork the repository and create a pull request for any enhancements or bug fixes.

License

This project is licensed under the MIT License. See the LICENSE file for more details.

