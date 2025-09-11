<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FPL Database Analysis Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .stat-card {
            border-left: 4px solid #007bff;
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .position-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        .form-indicator {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 0.8rem;
        }
        .navbar-brand {
            font-weight: 700;
            color: #37003c !important;
        }
        .table-responsive {
            max-height: 400px;
            overflow-y: auto;
        }
        .query-section {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        }
        .sql-badge {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-database me-2"></i>
                FPL Database Analysis Dashboard
            </a>
            <span class="navbar-text">
                <i class="fas fa-calendar-alt me-1"></i>
                Gameweek {{ $current_gameweek ?? 'N/A' }}
            </span>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        @if(isset($error))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                {{ $error }}
            </div>
        @else
            <!-- SQL Operations Overview -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>SQL Operations Implemented</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2 text-center">
                                    <span class="sql-badge">CRUD</span>
                                    <p class="mt-2 small">Create, Read, Update, Delete</p>
                                </div>
                                <div class="col-md-2 text-center">
                                    <span class="sql-badge">JOINS</span>
                                    <p class="mt-2 small">Inner, Left, Complex</p>
                                </div>
                                <div class="col-md-2 text-center">
                                    <span class="sql-badge">SUBQUERIES</span>
                                    <p class="mt-2 small">Correlated & Nested</p>
                                </div>
                                <div class="col-md-2 text-center">
                                    <span class="sql-badge">GROUP BY</span>
                                    <p class="mt-2 small">With HAVING clauses</p>
                                </div>
                                <div class="col-md-2 text-center">
                                    <span class="sql-badge">VIEWS</span>
                                    <p class="mt-2 small">Virtual Tables</p>
                                </div>
                                <div class="col-md-2 text-center">
                                    <span class="sql-badge">AGGREGATES</span>
                                    <p class="mt-2 small">SUM, AVG, COUNT, etc.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- JOINS DEMONSTRATION -->
            <div class="query-section">
                <div class="row">
                    <div class="col-12">
                        <h4 class="text-primary mb-3">
                            <i class="fas fa-link me-2"></i>
                            COMPLEX JOINS - Top Performers with Team Information
                        </h4>
                        <p class="text-muted mb-3">Using INNER JOINs across players, player_stats, and teams tables</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Player</th>
                                        <th>Position</th>
                                        <th>Team</th>
                                        <th>Points</th>
                                        <th>Cost (£m)</th>
                                        <th>Ownership (%)</th>
                                        <th>Form</th>
                                        <th>Goals</th>
                                        <th>Assists</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($top_performers ?? [] as $player)
                                    <tr>
                                        <td class="fw-bold">{{ $player->web_name }}</td>
                                        <td>
                                            <span class="badge 
                                                @if($player->position_full == 'Goalkeeper') bg-warning
                                                @elseif($player->position_full == 'Defender') bg-success  
                                                @elseif($player->position_full == 'Midfielder') bg-info
                                                @else bg-danger
                                                @endif position-badge">
                                                {{ $player->position_full }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong>{{ $player->team_short }}</strong>
                                            <small class="text-muted d-block">{{ $player->team_name }}</small>
                                        </td>
                                        <td><strong>{{ $player->total_points }}</strong></td>
                                        <td>£{{ number_format($player->cost_millions, 1) }}</td>
                                        <td>{{ number_format($player->selected_by_percent, 1) }}%</td>
                                        <td>
                                            <div class="form-indicator 
                                                @if($player->form >= 7) bg-success
                                                @elseif($player->form >= 5) bg-warning  
                                                @else bg-danger
                                                @endif">
                                                {{ number_format($player->form, 1) }}
                                            </div>
                                        </td>
                                        <td>{{ $player->goals_scored }}</td>
                                        <td>{{ $player->assists }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="9" class="text-center text-muted">No data available</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SUBQUERIES & WINDOW FUNCTIONS -->
            <div class="query-section">
                <div class="row">
                    <div class="col-12">
                        <h4 class="text-success mb-3">
                            <i class="fas fa-layer-group me-2"></i>
                            SUBQUERIES - Team Form Table (Last 5 Gameweeks)
                        </h4>
                        <p class="text-muted mb-3">Complex subqueries calculating recent form with nested SELECT statements</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-8">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-success">
                                    <tr>
                                        <th>Team</th>
                                        <th>Played</th>
                                        <th>W-D-L</th>
                                        <th>Goals F-A</th>
                                        <th>GD</th>
                                        <th>Points</th>
                                        <th>%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($form_table ?? [] as $team)
                                    <tr>
                                        <td><strong>{{ $team->short_name }}</strong></td>
                                        <td>{{ $team->matches_played }}</td>
                                        <td>{{ $team->wins }}-{{ $team->draws }}-{{ $team->losses }}</td>
                                        <td>{{ $team->goals_for }}-{{ $team->goals_against }}</td>
                                        <td class="@if($team->goal_difference > 0) text-success @elseif($team->goal_difference < 0) text-danger @endif">
                                            {{ $team->goal_difference > 0 ? '+' : '' }}{{ $team->goal_difference }}
                                        </td>
                                        <td><strong>{{ $team->form_points }}</strong></td>
                                        <td>{{ number_format($team->points_percentage, 1) }}%</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="7" class="text-center text-muted">No form data available</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">Form Analysis</h6>
                            </div>
                            <div class="card-body">
                                @if(isset($form_table) && count($form_table) > 0)
                                    <p><strong>Best Form:</strong> {{ $form_table[0]->short_name }}</p>
                                    <p><strong>Points:</strong> {{ $form_table[0]->form_points }}/{{ $form_table[0]->matches_played * 3 }}</p>
                                    <p><strong>Win Rate:</strong> {{ round(($form_table[0]->wins / $form_table[0]->matches_played) * 100, 1) }}%</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GROUP BY & HAVING CLAUSES -->
            <div class="query-section">
                <div class="row">
                    <div class="col-12">
                        <h4 class="text-warning mb-3">
                            <i class="fas fa-filter me-2"></i>
                            GROUP BY & HAVING - High Possession Teams
                        </h4>
                        <p class="text-muted mb-3">Teams grouped by possession stats with HAVING clause filtering > 50%</p>
                    </div>
                </div>
                <div class="row">
                    @forelse($high_possession_teams ?? [] as $team)
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <h6 class="card-title">{{ $team->name }}</h6>
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="h4 text-primary mb-0">{{ $team->avg_possession }}%</div>
                                        <small class="text-muted">Possession</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="h6 mb-0">{{ $team->avg_passes }}</div>
                                        <small class="text-muted">Avg Passes</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="h6 mb-0">{{ $team->pass_accuracy }}%</div>
                                        <small class="text-muted">Accuracy</small>
                                    </div>
                                </div>
                                <small class="text-muted">{{ $team->matches_played }} matches analyzed</small>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12">
                        <p class="text-center text-muted">No possession data available</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- AGGREGATE FUNCTIONS -->
            <div class="row">
                <div class="col-lg-6">
                    <div class="query-section">
                        <h4 class="text-danger mb-3">
                            <i class="fas fa-calculator me-2"></i>
                            AGGREGATES - Team Attacking Stats
                        </h4>
                        <p class="text-muted mb-3">SUM, AVG, COUNT functions for comprehensive analysis</p>
                        
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead class="table-danger">
                                    <tr>
                                        <th>Team</th>
                                        <th>Goals</th>
                                        <th>Avg Goals</th>
                                        <th>Shots</th>
                                        <th>Accuracy%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($team_attacking_stats ?? [] as $team)
                                    <tr>
                                        <td><strong>{{ $team->name }}</strong></td>
                                        <td>{{ $team->goals_scored }}</td>
                                        <td>{{ $team->avg_goals }}</td>
                                        <td>{{ $team->total_shots }}</td>
                                        <td>{{ $team->shot_accuracy }}%</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="5" class="text-center text-muted">No attacking data</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="query-section">
                        <h4 class="text-success mb-3">
                            <i class="fas fa-shield-alt me-2"></i>
                            AGGREGATES - Team Defensive Stats
                        </h4>
                        <p class="text-muted mb-3">Complex aggregations with CASE statements</p>
                        
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead class="table-success">
                                    <tr>
                                        <th>Team</th>
                                        <th>Clean Sheets</th>
                                        <th>CS%</th>
                                        <th>Avg Conceded</th>
                                        <th>Tackles</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($team_defensive_stats ?? [] as $team)
                                    <tr>
                                        <td><strong>{{ $team->short_name }}</strong></td>
                                        <td>{{ $team->clean_sheets }}</td>
                                        <td>{{ $team->clean_sheet_percentage }}%</td>
                                        <td>{{ $team->avg_goals_conceded }}</td>
                                        <td>{{ $team->total_tackles }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="5" class="text-center text-muted">No defensive data</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ADVANCED ANALYTICS -->
            <div class="row">
                <div class="col-lg-6">
                    <div class="query-section">
                        <h4 class="text-info mb-3">
                            <i class="fas fa-star me-2"></i>
                            Captain Recommendations
                        </h4>
                        <p class="text-muted mb-3">Multi-factor analysis using CTEs and complex scoring</p>
                        
                        @forelse($captain_recommendations ?? [] as $captain)
                        <div class="card mb-2 @if($captain->recommendation_level == 'Premium') border-warning @elseif($captain->recommendation_level == 'Excellent') border-success @endif">
                            <div class="card-body py-2">
                                <div class="row align-items-center">
                                    <div class="col-8">
                                        <h6 class="mb-0">{{ $captain->web_name }}</h6>
                                        <small class="text-muted">{{ $captain->team }} vs {{ $captain->opponent }} ({{ $captain->venue }})</small>
                                    </div>
                                    <div class="col-4 text-end">
                                        <span class="badge 
                                            @if($captain->recommendation_level == 'Premium') bg-warning
                                            @elseif($captain->recommendation_level == 'Excellent') bg-success
                                            @elseif($captain->recommendation_level == 'Good') bg-info
                                            @else bg-secondary
                                            @endif">
                                            {{ $captain->recommendation_level }}
                                        </span>
                                        <div><small class="text-muted">Score: {{ $captain->captain_score }}</small></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <p class="text-center text-muted">No captain recommendations available</p>
                        @endforelse
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="query-section">
                        <h4 class="text-secondary mb-3">
                            <i class="fas fa-gem me-2"></i>
                            Differential Players
                        </h4>
                        <p class="text-muted mb-3">Low ownership gems with statistical analysis</p>
                        
                        @forelse($differential_players ?? [] as $player)
                        <div class="card mb-2">
                            <div class="card-body py-2">
                                <div class="row align-items-center">
                                    <div class="col-7">
                                        <h6 class="mb-0">{{ $player->web_name }}</h6>
                                        <small class="text-muted">{{ $player->team }} • {{ $player->position }}</small>
                                    </div>
                                    <div class="col-5 text-end">
                                        <div><strong>{{ $player->ownership }}%</strong> <small class="text-muted">owned</small></div>
                                        <div><small class="text-success">£{{ $player->cost }}m • {{ $player->points_per_million }} pts/£m</small></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <p class="text-center text-muted">No differential players found</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- VIEWS AND TRANSFER RECOMMENDATIONS -->
            <div class="row">
                <div class="col-12">
                    <div class="query-section">
                        <h4 class="text-dark mb-3">
                            <i class="fas fa-exchange-alt me-2"></i>
                            VIEWS INTEGRATION - Transfer Recommendations
                        </h4>
                        <p class="text-muted mb-3">Data from database views combined with complex analysis</p>
                        
                        <div class="row">
                            @forelse($transfer_recommendations ?? [] as $transfer)
                            <div class="col-lg-6 mb-3">
                                <div class="card 
                                    @if(str_contains($transfer->recommendation, 'BUY')) border-success
                                    @elseif(str_contains($transfer->recommendation, 'SELL')) border-danger
                                    @endif">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">{{ $transfer->web_name }}</h6>
                                                <small class="text-muted">{{ $transfer->team }} • {{ $transfer->position }}</small>
                                            </div>
                                            <span class="badge 
                                                @if(str_contains($transfer->recommendation, 'BUY')) bg-success
                                                @elseif(str_contains($transfer->recommendation, 'SELL')) bg-danger
                                                @endif fs-6">
                                                {{ explode(' - ', $transfer->recommendation)[0] }}
                                            </span>
                                        </div>
                                        <hr class="my-2">
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <div class="small text-muted">Form</div>
                                                <div>{{ $transfer->form }}</div>
                                            </div>
                                            <div class="col-4">
                                                <div class="small text-muted">Cost</div>
                                                <div>£{{ number_format($transfer->current_cost, 1) }}m</div>
                                            </div>
                                            <div class="col-4">
                                                <div class="small text-muted">Owned</div>
                                                <div>{{ number_format($transfer->ownership, 1) }}%</div>
                                            </div>
                                        </div>
                                        <p class="small text-muted mt-2 mb-0">{{ explode(' - ', $transfer->recommendation)[1] ?? $transfer->recommendation }}</p>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-12">
                                <p class="text-center text-muted">No transfer recommendations at this time</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- API Endpoints Information -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="fas fa-code me-2"></i>Available API Endpoints</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-primary">Query Operations</h6>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item"><code>GET /fpl/joins</code> - Complex JOIN queries</li>
                                        <li class="list-group-item"><code>GET /fpl/subqueries</code> - Subquery examples</li>
                                        <li class="list-group-item"><code>GET /fpl/groupby</code> - GROUP BY with HAVING</li>
                                        <li class="list-group-item"><code>GET /fpl/aggregates</code> - Aggregate functions</li>
                                        <li class="list-group-item"><code>POST /fpl/crud</code> - CRUD operations demo</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-success">Analysis Endpoints</h6>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item"><code>GET /fpl/captains/{gameweek}</code> - Captain analysis</li>
                                        <li class="list-group-item"><code>GET /fpl/differentials</code> - Differential players</li>
                                        <li class="list-group-item"><code>GET /fpl/clean-sheets/{gameweek}</code> - CS probabilities</li>
                                        <li class="list-group-item"><code>GET /fpl/transfers</code> - Transfer recommendations</li>
                                        <li class="list-group-item"><code>POST /fpl/views</code> - Create/manage views</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
