from os import system

models = ['User','Places','Buildings','Rooms','RoomDetails','Notes','Statuses',
          'Reservesions','Orders','AttendenceLog','AbsenceRequests','Tickets',
          'Documents',]

for model in models:
    print('Creating Model for: ',model)
    system('php artisan make:model '+model+' -a')
    print('Done.')