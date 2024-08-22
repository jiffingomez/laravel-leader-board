# Leaderboard APP

![leader-board](https://github.com/user-attachments/assets/80d8a119-5814-4025-ad7c-0e0b29b7defe)

## Introduction
This README file provides an overview of Leaderboard application, including installation instructions, usage guidelines, and important information for developers.

Author: Jiffin Joachim Gomez (jiffingomez@gmail.com)

### Dependencies
PHP version 8.2  
Laravel Framework 11.21.0  
SQLite
### Installation
1. Clone the Repository:
  

    git clone
    https://github.com/jiffingomez/laravel-leader-board.git
    

2. Install Dependencies:  
  

    cd laravel-leader-board
    composer install

3. Configure Environment:
   

    Create a .env file by copying .env.example.
    Update the environment variables with your project-specific values, such as database credentials and application URLs.  
  

4. Run Database Migrations:  
  
  
    php artisan migrate:refresh --seed


5. Command to reset leader board points to zero
  

    php artisan app:reset-leader-board-points

6. Run the queue (Optional)
  

    php artisan queue:work

7. Run the job (Optional)
  

    php artisan schedule:work

8. Start the Development Server:


    php artisan serve
9. Access the Application:
  

    Open your web browser and navigate to http://localhost:8000.

### Additional Notes
Environment Variables: Refer to the .env.example file for a list of available environment variables and their descriptions.
Routing: The application's routes are defined in routes/web.php, routes/api.php.
Controllers: Controllers are located in the app/Http/Controllers directory.
Views: Blade templates are stored in the resources/views directory.

### Contributing
If you'd like to contribute to this project, please follow these guidelines:

Fork the Repository: Create a fork of the project on GitHub.
Create a Branch: Create a new branch for your feature or bug fix.
Make Changes: Implement your changes and commit them to your branch.
Submit a Pull Request: Submit a pull request to the main repository, describing your changes.


### Additional Information
Documentation: http://laravel-leader-board.test/api/documentation#/Leaderboard  

  ![Screenshot 2024-08-22 220502](https://github.com/user-attachments/assets/37463975-0002-47db-8cb1-e081e230becf)


Contact: jiffingomez@gmail.com
