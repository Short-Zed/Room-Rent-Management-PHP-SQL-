# Room Rent Calculator

![Room Rent Calculator](https://github.com/Short-Zed/Room-Rent-Management-PHP-SQL-/blob/main/Macbook-Air-room-rent-management.infinityfreeapp.com.png?raw=true)  
*A simple web application to calculate and manage room rent with electricity bills.*

## Overview

Room Rent Calculator is a PHP-based web application designed to help users calculate their monthly room rent, including electricity costs based on unit consumption. It allows users to add, edit, delete, and download rent records as PDFs, with a graphical trend view using Chart.js. Records are displayed in descending order by date for easy tracking of the latest entries.

### Features
- **Calculate Rent**: Input previous and current electricity units, unit price, room rent, and optional additional/subtraction amounts.
- **Record Management**: View, edit, and delete records, with the latest entries shown first.
- **PDF Export**: Download all or selected records as a PDF using TCPDF.
- **Graphical Insights**: Visualize rent trends over time with Chart.js.
- **User Authentication**: Secure login system to manage personal records.

## Tech Stack
- **Frontend**: HTML, CSS, JavaScript (Chart.js)
- **Backend**: PHP
- **Database**: MySQL (via PDO)
- **PDF Generation**: TCPDF
- **Hosting**: Compatible with free hosting platforms like InfinityFree

## Getting Started

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (e.g., Apache) with PHP support
- Composer (optional, for TCPDF installation)

### Installation
1. **Clone the Repository**  
   ```bash
   git clone https://github.com/your-username/room-rent-calculator.git
   cd room-rent-calculator
