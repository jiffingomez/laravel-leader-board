### Step 1 : Create LeaderBoard Table
    Create the leader board table by running the below command

    php artisan make:model LeaderBoard -m

### Step 2 Run the migration

    Run the migration to create the table in your database:
    php artisan migrate

### Step 3 Create Factory
    Run the below command to create a factory method:
    php artisan make:factory LeaderBoardFactory --model=LeaderBoard

### Step 4: Create a Seeder
    Run the below command to create a seeder
    php artisan make:seed LeaderBoardSeeder

### Step 5: Populate the LeaderBoard
    Run the below command to seed the data
    php artisan db:seed --class=LeaderBoardSeeder

### Step 6: Optional To Refresh Seed (To clear the data)
    php artisan migrate:refresh --seed

### Step 7: Generate Controller
    Generate a controller for handling leader board related API requests
    php artisan make:controller LeaderBoardController

### Step 8: Create All the controller fucntions for the crud options

### Step 9: Create a Command to reset Points to 0
    Run the below command to create a command
    php artisan make:command ResetLeaderBoardPoints

### Step 10: Run Command to reset leader board points
    php artisan app:reset-leader-board-points

### Step 11: Adding Request Validations
    php artisan make:request UpdateUserPoints

### step 12: Setup Queue
    php artisan make:queue-table

### Step 13: Create A Job
    php artisan make:job QRCodeGenerator

### Step 14: Run Queue (Needs to enable it)
    php artisan queue:work

### Step 15: publish job
    php artisan make:job PublishWinner

## Step 16: Running the scheduler locally
    php artisan schedule:work
