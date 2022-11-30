<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enum\RoutePointEnum;
use App\Http\Models\Point;
use App\Http\Requests\LineRequest;
use App\Http\Requests\PlacesRequest;
use App\Http\Requests\RoutesRequest;
use App\Http\Resources\LineResource;
use App\Http\Resources\PlacesPointResource;
use App\Http\Resources\RoutePointResource;
use App\Models\Line;
use App\Models\LineIntersection;
use App\Models\PlacesPoint;
use App\Models\RoutePoint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Constraint\Count;

class AlRashidController extends Controller
{
    public function getLines(Request $request)
    {
        $lines = LineResource::collection(Line::all());

        return $lines;
    }

    public function createLines(LineRequest $request)
    {
        $line = Line::create($request->createArray());

        return new LineResource($line);
    }

    public function updateLines(LineRequest $request, int $id)
    {
        Line::whereId($id)->update($request->createArray());

        return new LineResource(Line::whereId($id)->firstOrFail());
    }

    public function deleteLine(Request $request, int $id)
    {
        Line::whereId($id)->delete();

        return response()->json(['status' => 200, 'message' => 'deleted']);
    }

    public function getAvailableIntersectionIds($nearestLine1Id, $nearestLine2Id)
    {
        $intersectionsLine1 = LineIntersection::where('line_id', $nearestLine1Id)->get();
        $intersectionsLine2 = LineIntersection::where('line_id', $nearestLine2Id)->get();

        $availableIntersectionsIds = [];
        foreach ($intersectionsLine1 as $intersection) {
            foreach ($intersectionsLine2 as $intersection2) {
                if ($intersection->intersection_line_id == $intersection2->intersection_line_id) {
                    $availableIntersectionsIds[] = $intersection->intersection_line_id;
                }
            }
        }

        return $availableIntersectionsIds;
    }

    //---------------------------------------------------------------------
    public function getRoutes(Request $request)
    {
        $fromId = $request->get('from_id', 7);
        $toId = $request->get('to_id', 6);
        if($fromId ==0 || $toId == 0)
        {
            return RoutePointResource::collection([]);
        }
        $placeStart = PlacesPoint::where('id', $fromId)->first();
        $placeEnd = PlacesPoint::where('id', $toId)->first();
        $points = [];
        $lines = Line::where('floor', $placeStart->floor)->where('is_intersection', 0)->get();

        $nearestLine1 = $this->getNearestLine($lines, $placeStart);
        $nearestLine2 = $this->getNearestLine($lines, $placeEnd);
        $IntersectionPoints = [];
        if ($nearestLine1->id != $nearestLine2->id) {
            $availableIntersectionsIds = $this->getAvailableIntersectionIds($nearestLine1->id, $nearestLine2->id);
            $intersections = LineIntersection::whereIn('intersection_line_id', $availableIntersectionsIds)
                ->where(function ($q) use ($nearestLine1, $nearestLine2) {
                    $q->where('line_id', $nearestLine1->id);
                    $q->orWhere('line_id', $nearestLine2->id);

                    return $q;
                })
                ->orderBy(
                    'intersection_line_id',
                    'ASC'
                )->get();
            $nearestIntersectionIndex = $this->nearestIntersectionIndex($intersections, $placeStart);

            $usedIntersections = [];
            $usedIntersections[] = $intersections[$nearestIntersectionIndex];
            $usedIntersections[] = $intersections[$nearestIntersectionIndex + 1];

            $expectedPoints = ($this->generateExpectedRoute($usedIntersections, rand(100, 200)));
            //--------

            $IntersectionPoints = $expectedPoints;
        }

        $routes = $nearestLine1->routes;
        $routes2 = $nearestLine2->routes;
        $expectedPoints = [];
        if ($nearestLine1->id != $nearestLine2->id) {
            $expectedPoints = array_merge(
                $expectedPoints,
                $this->getRouteExpectedRoutes($routes, $placeStart, $usedIntersections[0])
            );
            $expectedPoints = array_merge(
                $expectedPoints,
                $this->getRouteExpectedRoutes($routes2, $placeEnd, $usedIntersections[1])
            );
        } else {
            $expectedPoints = $this->getRouteExpectedRoutes($routes, $placeStart, $placeEnd);
        }

        foreach ($IntersectionPoints as $intersectionPoint) {
            $expectedPoints[] = $intersectionPoint;
        }
        for ($i = 0 ; $i < count($expectedPoints) ; $i++) {
            $routePoint = $this->generateRoutePoint();
            $routePoint->x_point = $expectedPoints[$i]->x;
            $routePoint->y_point = $expectedPoints[$i]->y;
            $routePoint->line_id = $expectedPoints[$i]->line_id;
            $points[] = $routePoint;
        }

        return RoutePointResource::collection($points);
    }

    public function getRouteExpectedRoutes($routes, $sPoint, $ePoint)
    {
        $expectedPoints = ($this->generateExpectedRoute($routes));
        $point1 = $this->enhancePointsToDisplay($sPoint);
        $point2 = $this->enhancePointsToDisplay($ePoint);

        $iStart = $this->findNearestPointIndex(
            $expectedPoints,
            $point1
        );

        $iEnd = $this->findNearestPointIndex(
            $expectedPoints,
            $point2
        );


        if ($iStart > $iEnd) {
            $tempSwap = $iStart;
            $iStart = $iEnd;
            $iEnd = $tempSwap;
        }

        return (array_values(array_slice($expectedPoints, $iStart, $iEnd - $iStart)));
    }


    public function createRoutes(RoutesRequest $request)
    {
        $place = RoutePoint::create($request->createArray());

        return new RoutePointResource($place);
    }

    public function updateRoutes(RoutesRequest $request, int $id)
    {
        RoutePoint::whereId($id)->update($request->createArray());

        return new RoutePointResource(RoutePoint::whereId($id)->firstOrFail());
    }

    public function deleteRoutes(Request $request, int $id)
    {
        RoutePoint::whereId($id)->delete();

        return response()->json(['status' => 200, 'message' => 'deleted']);
    }

    //-------------------------------------------------------------------------
    private function generateRoutePoint()
    {
        $routePoint = new PlacesPoint();
        $routePoint->id = 1;
        $routePoint->name = 'test';
        $routePoint->code = 'test';
        $routePoint->type = 'SHOP';
        $routePoint->floor = 1;
        $routePoint->screen_width = RoutePointEnum::SCREEN_WIDTH;
        $routePoint->screen_height = RoutePointEnum::SCREEN_HEIGHT;

        return $routePoint;
    }


    public function getPlacesPoints(Request $request): AnonymousResourceCollection
    {
        $floor = $request->get('floor', 1);
        $this->generateIntersectionLines($floor);
        return PlacesPointResource::collection(PlacesPoint::where('floor', $floor)->get());
    }

    public function createPlacesPoints(PlacesRequest $request)
    {
        $place = PlacesPoint::create($request->createArray());

        return new PlacesPointResource($place);
    }

    public function updatePlacesPoints(PlacesRequest $request, int $id): PlacesPointResource
    {
        PlacesPoint::whereId($id)->update($request->createArray());

        return new PlacesPointResource(PlacesPoint::whereId($id)->firstOrFail());
    }

    public function deletePlacesPoints(Request $request, int $id): JsonResponse
    {
        PlacesPoint::whereId($id)->delete();

        return response()->json(['status' => 200, 'message' => 'deleted']);
    }

    public function generateExpectedRoute($routes, $lineId = null): array
    {
        $points = [];
        for ($r1 = 0 ; $r1 < Count($routes) ; $r1++) {
            if (Count($routes) <= $r1 + 1) {
                continue;
            }
            $PR1 = $this->enhancePointsToDisplay($routes[$r1]);
            $PR2 = $this->enhancePointsToDisplay($routes[$r1 + 1]);
            $lineId = $lineId ?? $routes[$r1]->line_id;
            $points = array_merge($points, $this->getPoints($PR1, $PR2, 50));
        }
        foreach ($points as &$point) {
            $point->line_id = $lineId;
        }

        return $points;
    }

    public function getPoints(Point $p1, Point $p2, int $quantity): array
    {
        $points = [];
        $ydiff = $p2->y - $p1->y;
        $xdiff = $p2->x - $p1->x;
        if ($p2->x - $p1->x == 0) {
            return [];
        }
        $slope = (double) ($p2->y - $p1->y) / ($p2->x - $p1->x);
        $x = 0;
        $y = 0;

        --$quantity;

        for ($i = 0 ; $i < $quantity ; $i++) {
            $y = $slope == 0 ? 0 : $ydiff * ($i / $quantity);
            $x = $slope == 0 ? $xdiff * ($i / $quantity) : $y / $slope;
            $points[$i] = new Point((int) round($x) + $p1->x, (int) round($y) + $p1->y);
        }
        $points[$quantity] = $p2;

        return $points;
    }

    public function generateIntersectionLines($floor = 1): array
    {
        $foundIntersections = [];
        $lines = Line::where('floor', $floor)->where('is_intersection', 0)->get();
        $intersectionLines = Line::where('floor', $floor)->where('is_intersection', 1)->get();
        foreach ($lines as $line) {
            $this->getLineIntersections($intersectionLines, $line);
        }

        return $foundIntersections;
    }

    private function getLineIntersections(Collection $intersectionLines, Line $line): void
    {
        if ($line->hasIntersections()) {
            return;
        }
        $foundIntersections = [];
        $routes = $line->routes;
        for ($k = 0 ; $k < count($intersectionLines) ; $k++) {
            $intersectRoutes = $intersectionLines[$k]->routes;
            for ($ir = 0 ; $ir < Count($intersectRoutes) ; $ir++) {
                if (Count($intersectRoutes) <= $ir + 1) {
                    continue;
                }

                $IL1 = $this->enhancePointsToDisplay($intersectRoutes[$ir]);
                $IL2 = $this->enhancePointsToDisplay($intersectRoutes[$ir + 1]);

                for ($r1 = 0 ; $r1 < Count($routes) ; $r1++) {
                    if (Count($routes) <= $r1 + 1) {
                        continue;
                    }
                    $x1Ratio = RoutePointEnum::SCREEN_WIDTH / $routes[$r1]->screen_width;
                    $y1Ratio = RoutePointEnum::SCREEN_HEIGHT / $routes[$r1]->screen_height;
                    $x2Ratio = RoutePointEnum::SCREEN_WIDTH / $routes[$r1 + 1]->screen_width;
                    $y2Ratio = RoutePointEnum::SCREEN_HEIGHT / $routes[$r1 + 1]->screen_height;
                    $PR1 = new Point(
                        ($routes[$r1]->x_point * $x1Ratio),
                        ($routes[$r1]->y_point * $y1Ratio)
                    );
                    $PR2 = new Point(
                        ($routes[$r1 + 1]->x_point * $x2Ratio),
                        ($routes[$r1 + 1]->y_point * $y2Ratio)
                    );


                    $intersectionLine = $this->lineLineIntersection($PR1, $PR2, $IL1, $IL2);
                    if ($intersectionLine != false) {
                        $lineId = $routes[$r1]->line_id;
                        LineIntersection::create(
                            [
                                'line_id' => $lineId,
                                'intersection_line_id' => $intersectRoutes[$ir]->line_id,
                                'x_point' => $intersectionLine->x,
                                'y_point' => $intersectionLine->y,
                                'screen_width' => RoutePointEnum::SCREEN_WIDTH,
                                'screen_height' => RoutePointEnum::SCREEN_HEIGHT,
                            ]
                        );
                        $foundIntersections[] = ($intersectionLine);
                    }
                }
            }
        }
    }

    private function lineLineIntersection(Point $A, Point $B, Point $C, Point $D): Point|false
    {
        $x1 = $A->x;
        $x2 = $B->x;
        $x3 = $C->x;
        $x4 = $D->x;
        $y1 = $A->y;
        $y2 = $B->y;
        $y3 = $C->y;
        $y4 = $D->y;

        // Check if none of the lines are of length 0
        if (($x1 === $x2 && $y1 === $y2) || ($x3 === $x4 && $y3 === $y4)) {
            return false;
        }

        $denominator = (($y4 - $y3) * ($x2 - $x1) - ($x4 - $x3) * ($y2 - $y1));

        // Lines are parallel
        if ($denominator === 0) {
            return false;
        }

        $ua = (($x4 - $x3) * ($y1 - $y3) - ($y4 - $y3) * ($x1 - $x3)) / $denominator;
        $ub = (($x2 - $x1) * ($y1 - $y3) - ($y2 - $y1) * ($x1 - $x3)) / $denominator;

        // is the intersection along the segments
        if ($ua < 0 || $ua > 1 || $ub < 0 || $ub > 1) {
            return false;
        }

        // Return a object with the $x and $y coordinates of the intersection
        $x = (int) ($x1 + $ua * ($x2 - $x1));
        $y = (int) ($y1 + $ua * ($y2 - $y1));

        return new Point($x, $y);
    }

    private function getNearestLine($lines, $place): array|Line
    {
        $lineDistance = 10000;
        $nearestLine = [];
        /** @var Line $line */
        foreach ($lines as $line) {
            $lineRoutes = $line->routes;
            $count = Count($lineRoutes);
            for ($i = 0 ; $i < $count ; $i++) {
                if ($i + 1 >= $count) {
                    continue;
                }

                $route1 = $lineRoutes[$i];
                $route2 = $lineRoutes[$i + 1];
                $x1Ratio = RoutePointEnum::SCREEN_WIDTH / $route1->screen_width;
                $y1Ratio = RoutePointEnum::SCREEN_HEIGHT / $route1->screen_height;

                $r1x = $route1->x_point * $x1Ratio;
                $r1y = $route1->y_point * $y1Ratio;

                $x2Ratio = RoutePointEnum::SCREEN_WIDTH / $route2->screen_width;
                $y2Ratio = RoutePointEnum::SCREEN_HEIGHT / $route2->screen_height;
                $r2x = $route2->x_point * $x2Ratio;
                $r2y = $route2->y_point * $y2Ratio;


                $pxRatio = RoutePointEnum::SCREEN_WIDTH / $place->screen_width;
                $pyRatio = RoutePointEnum::SCREEN_HEIGHT / $place->screen_height;
                $px = $place->x_point * $pxRatio;
                $py = $place->y_point * $pyRatio;
                $distance = $this->minDistance(new Point($r1x, $r1y), new Point($r2x, $r2y), new Point($px, $py));
                //
                //$distance = $this->calculateDistance(
                //    $r1x,
                //    $r1y,
                //    $r2x,
                //    $r2y,
                //    $px,
                //    $py
                //);

                if ($distance < $lineDistance) {
                    $lineDistance = $distance;
                    $nearestLine = $line;
                }
            }
        }

        return $nearestLine;
    }

    function minDistance(Point $A, Point $B, Point $E): float|int
    {
        // vector AB
        $AB = new Point($B->x - $A->x, $B->y - $A->y);
        $AE = new Point($E->x - $A->x, $E->y - $A->y);
        $BE = new Point($E->x - $B->x, $E->y - $B->y);
        $AB_BE = $AB->x * $BE->x + $AB->y * $BE->y;
        $AB_AE = $AB->x * $AE->x + $AB->y * $AE->y;


        // Minimum distance from
        // point E to the line segment
        $reqAns = 0;

        // Case 1
        if ($AB_BE > 0) {
            // Finding the magnitude
            $y = $E->y - $B->y;
            $x = $E->x - $B->x;
            $reqAns = sqrt($x * $x + $y * $y);
        } // Case 2
        elseif ($AB_AE < 0) {
            $y = $E->y - $A->y;
            $x = $E->x - $A->x;
            $reqAns = sqrt($x * $x + $y * $y);
        } // Case 3
        else {
            // Finding the perpendicular distance
            $x1 = $AB->x;
            $y1 = $AB->y;
            $x2 = $AE->x;
            $y2 = $AE->y;
            $mod = sqrt($x1 * $x1 + $y1 * $y1);
            $reqAns = abs($x1 * $y2 - $y1 * $x2) / $mod;
        }

        return $reqAns;
    }

    public function enhancePointsToDisplay($pointsDetails): Point
    {
        $x1Ratio = RoutePointEnum::SCREEN_WIDTH / $pointsDetails->screen_width;
        $y1Ratio = RoutePointEnum::SCREEN_HEIGHT / $pointsDetails->screen_height;
        $px1 = $pointsDetails->x_point * $x1Ratio;
        $py1 = $pointsDetails->y_point * $y1Ratio;

        return new Point($px1, $py1);
    }

    function nearestIntersectionIndex($intersections, $placeStart): int
    {
        $nearestIntersectionIndex = 1000000;
        $nearestIntersectionDistance = 1000000;

        for ($i = 0 ; $i < count($intersections) ; $i++) {
            if ($i + 1 >= count(
                    $intersections
                ) || $intersections[$i]->intersection_line_id != $intersections[$i + 1]->intersection_line_id) {
                continue;
            }

            $intersectPoint1 = $this->enhancePointsToDisplay($intersections[$i]);
            $intersectPoint2 = $this->enhancePointsToDisplay($intersections[$i + 1]);
            $point = $this->enhancePointsToDisplay($placeStart);

            $distance = $this->minDistance($intersectPoint1, $intersectPoint2, $point);

            if ($nearestIntersectionDistance > $distance) {
                $nearestIntersectionDistance = $distance;
                $nearestIntersectionIndex = $i;
            }
        }

        return $nearestIntersectionIndex;
    }

    function findNearestPointIndex($points, Point $point, $debug = false): int
    {
        $nearestDistance = 1000000;
        $nearestIndex = 100000;
        for ($i = 0 ; $i < Count($points) ; $i++) {
            $distance = $this->findDistanceBetweenPoints(
                new Point(
                    $points[$i]->x,
                    $points[$i]->y
                ),
                $point
            );
            if ($debug) {
                dump("i: " . $i . " distance: " . $distance);
                dump("x: " . $points[$i]->x . " y: " . $points[$i]->y);
                dump("xp: " . $point->x . " yp: " . $point->y);
            }
            if ($distance < $nearestDistance) {
                $nearestDistance = $distance;
                $nearestIndex = $i;
            }
        }

        return $nearestIndex;
    }

    function findDistanceBetweenPoints(Point $A, Point $B): float
    {
        $dh = $A->y - $B->y;
        $dw = $A->x - $B->x;

        return sqrt($dh * $dh + $dw * $dw);
    }

}

