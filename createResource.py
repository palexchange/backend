from os import listdir, system
onlyfiles = [f.removesuffix('Controller.php') for f in listdir('app\Http\Controllers') if f != 'Controller.php']
for file in onlyfiles:
    print('Creating Resource for: ',file)
    system('php artisan make:resource '+file+'Resource')
    print('Finish.')