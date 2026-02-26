@echo off
cd /d %~dp0
php -r "
require 'vendor/autoload.php';
\$app = require 'bootstrap/app.php';
\$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use App\Http\Controllers\CargoAutoPlacementController;
use Illuminate\Http\Request;

\$controller = new CargoAutoPlacementController();
\$request = new Request(['voyage_id' => 1]);
\$response = \$controller->getPackingData(\$request);

echo json_encode(\$response->getData(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
"
