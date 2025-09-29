<!-- Additional Modals for Enhanced Dashboard -->

<!-- Fitness Profile Modal -->
<div class="modal fade" id="fitnessModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">💪 Fitness Profile Setup</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="fitnessForm">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Height (cm)</label>
                <input type="number" class="form-control" name="height" step="0.1" min="100" max="250" 
                       value="<?php echo $fitness_profile['height'] ?? ''; ?>">
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Target Weight (kg)</label>
                <input type="number" class="form-control" name="target_weight" step="0.1" min="30" max="200"
                       value="<?php echo $fitness_profile['target_weight'] ?? ''; ?>">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Fitness Level</label>
                <select class="form-control" name="fitness_level">
                  <option value="beginner" <?php echo ($fitness_profile['fitness_level'] ?? '') === 'beginner' ? 'selected' : ''; ?>>Beginner</option>
                  <option value="intermediate" <?php echo ($fitness_profile['fitness_level'] ?? '') === 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                  <option value="advanced" <?php echo ($fitness_profile['fitness_level'] ?? '') === 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Primary Goal</label>
                <select class="form-control" name="primary_goal">
                  <option value="weight_loss" <?php echo ($fitness_profile['primary_goal'] ?? '') === 'weight_loss' ? 'selected' : ''; ?>>Weight Loss</option>
                  <option value="muscle_gain" <?php echo ($fitness_profile['primary_goal'] ?? '') === 'muscle_gain' ? 'selected' : ''; ?>>Muscle Gain</option>
                  <option value="strength" <?php echo ($fitness_profile['primary_goal'] ?? '') === 'strength' ? 'selected' : ''; ?>>Strength</option>
                  <option value="endurance" <?php echo ($fitness_profile['primary_goal'] ?? '') === 'endurance' ? 'selected' : ''; ?>>Endurance</option>
                  <option value="general_fitness" <?php echo ($fitness_profile['primary_goal'] ?? '') === 'general_fitness' ? 'selected' : ''; ?>>General Fitness</option>
                </select>
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Activity Level</label>
            <select class="form-control" name="activity_level">
              <option value="sedentary" <?php echo ($fitness_profile['activity_level'] ?? '') === 'sedentary' ? 'selected' : ''; ?>>Sedentary (little to no exercise)</option>
              <option value="lightly_active" <?php echo ($fitness_profile['activity_level'] ?? '') === 'lightly_active' ? 'selected' : ''; ?>>Lightly Active (light exercise 1-3 days/week)</option>
              <option value="moderately_active" <?php echo ($fitness_profile['activity_level'] ?? '') === 'moderately_active' ? 'selected' : ''; ?>>Moderately Active (moderate exercise 3-5 days/week)</option>
              <option value="very_active" <?php echo ($fitness_profile['activity_level'] ?? '') === 'very_active' ? 'selected' : ''; ?>>Very Active (hard exercise 6-7 days/week)</option>
              <option value="extra_active" <?php echo ($fitness_profile['activity_level'] ?? '') === 'extra_active' ? 'selected' : ''; ?>>Extra Active (very hard exercise, physical job)</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Medical Conditions (optional)</label>
            <textarea class="form-control" name="medical_conditions" rows="2" placeholder="Any medical conditions we should know about..."><?php echo $fitness_profile['medical_conditions'] ?? ''; ?></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Profile</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Nutrition Plan Modal -->
<div class="modal fade" id="nutritionModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">🥗 Nutrition Plan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="nutritionForm">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Plan Name</label>
            <input type="text" class="form-control" name="plan_name" required 
                   value="<?php echo $nutrition_plan['plan_name'] ?? ''; ?>" 
                   placeholder="e.g., My Weight Loss Plan">
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Diet Type</label>
                <select class="form-control" name="diet_type" required>
                  <option value="standard" <?php echo ($nutrition_plan['diet_type'] ?? '') === 'standard' ? 'selected' : ''; ?>>Standard</option>
                  <option value="vegetarian" <?php echo ($nutrition_plan['diet_type'] ?? '') === 'vegetarian' ? 'selected' : ''; ?>>Vegetarian</option>
                  <option value="vegan" <?php echo ($nutrition_plan['diet_type'] ?? '') === 'vegan' ? 'selected' : ''; ?>>Vegan</option>
                  <option value="keto" <?php echo ($nutrition_plan['diet_type'] ?? '') === 'keto' ? 'selected' : ''; ?>>Keto</option>
                  <option value="paleo" <?php echo ($nutrition_plan['diet_type'] ?? '') === 'paleo' ? 'selected' : ''; ?>>Paleo</option>
                  <option value="mediterranean" <?php echo ($nutrition_plan['diet_type'] ?? '') === 'mediterranean' ? 'selected' : ''; ?>>Mediterranean</option>
                  <option value="low_carb" <?php echo ($nutrition_plan['diet_type'] ?? '') === 'low_carb' ? 'selected' : ''; ?>>Low Carb</option>
                  <option value="high_protein" <?php echo ($nutrition_plan['diet_type'] ?? '') === 'high_protein' ? 'selected' : ''; ?>>High Protein</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Daily Calories</label>
                <input type="number" class="form-control" name="daily_calories" min="1000" max="5000"
                       value="<?php echo $nutrition_plan['daily_calories'] ?? ''; ?>" 
                       placeholder="e.g., 2200">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Daily Protein (g)</label>
                <input type="number" class="form-control" name="daily_protein" step="0.1" min="0"
                       value="<?php echo $nutrition_plan['daily_protein'] ?? ''; ?>" 
                       placeholder="e.g., 140">
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Daily Carbs (g)</label>
                <input type="number" class="form-control" name="daily_carbs" step="0.1" min="0"
                       value="<?php echo $nutrition_plan['daily_carbs'] ?? ''; ?>" 
                       placeholder="e.g., 200">
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Daily Fat (g)</label>
                <input type="number" class="form-control" name="daily_fat" step="0.1" min="0"
                       value="<?php echo $nutrition_plan['daily_fat'] ?? ''; ?>" 
                       placeholder="e.g., 75">
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Meals Per Day</label>
            <select class="form-control" name="meals_per_day">
              <option value="3" <?php echo ($nutrition_plan['meals_per_day'] ?? 3) == 3 ? 'selected' : ''; ?>>3 Meals</option>
              <option value="4" <?php echo ($nutrition_plan['meals_per_day'] ?? 3) == 4 ? 'selected' : ''; ?>>4 Meals</option>
              <option value="5" <?php echo ($nutrition_plan['meals_per_day'] ?? 3) == 5 ? 'selected' : ''; ?>>5 Meals</option>
              <option value="6" <?php echo ($nutrition_plan['meals_per_day'] ?? 3) == 6 ? 'selected' : ''; ?>>6 Meals</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Food Allergies (optional)</label>
            <input type="text" class="form-control" name="food_allergies" 
                   placeholder="e.g., nuts, dairy, shellfish (comma separated)"
                   value="<?php echo isset($nutrition_plan['food_allergies']) ? htmlspecialchars(str_replace(['["', '"]', '","'], ['', '', ', '], $nutrition_plan['food_allergies'])) : ''; ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Food Preferences (optional)</label>
            <input type="text" class="form-control" name="food_preferences" 
                   placeholder="e.g., high protein, low sugar, whole grains (comma separated)"
                   value="<?php echo isset($nutrition_plan['food_preferences']) ? htmlspecialchars(str_replace(['["', '"]', '","'], ['', '', ', '], $nutrition_plan['food_preferences'])) : ''; ?>">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Plan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Goals Management Modal -->
<div class="modal fade" id="goalsModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">🎯 Manage Goals</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6>Current Goals</h6>
          <button type="button" class="btn btn-sm btn-primary" onclick="showAddGoalForm()">
            <i class="bi bi-plus"></i> Add Goal
          </button>
        </div>
        
        <!-- Goals List -->
        <div id="goalsList">
          <?php foreach($active_goals as $goal): ?>
            <div class="goal-item-modal mb-3" data-goal-id="<?php echo $goal['id']; ?>">
              <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                  <h6 class="mb-1"><?php echo htmlspecialchars($goal['title']); ?></h6>
                  <p class="mb-1 text-muted small"><?php echo htmlspecialchars($goal['description']); ?></p>
                  <?php if ($goal['target_value'] && $goal['current_value'] !== null): ?>
                    <div class="progress mb-2" style="height: 6px;">
                      <?php 
                      $progress = $goal['target_value'] != 0 ? min(100, ($goal['current_value'] / $goal['target_value']) * 100) : 0;
                      ?>
                      <div class="progress-bar" style="width: <?php echo $progress; ?>%"></div>
                    </div>
                    <small class="text-muted">
                      <?php echo number_format($goal['current_value'], 1); ?> / <?php echo number_format($goal['target_value'], 1); ?> <?php echo htmlspecialchars($goal['unit']); ?>
                    </small>
                  <?php endif; ?>
                </div>
                <div class="ms-3">
                  <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="updateGoalProgress(<?php echo $goal['id']; ?>)">
                    Update
                  </button>
                  <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteGoal(<?php echo $goal['id']; ?>)">
                    Delete
                  </button>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        
        <!-- Add Goal Form (Hidden by default) -->
        <div id="addGoalForm" style="display: none;">
          <hr>
          <h6>Add New Goal</h6>
          <form id="newGoalForm">
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Goal Type</label>
                  <select class="form-control" name="goal_type" required>
                    <option value="weight">Weight</option>
                    <option value="strength">Strength</option>
                    <option value="endurance">Endurance</option>
                    <option value="habit">Habit</option>
                    <option value="body_measurement">Body Measurement</option>
                    <option value="performance">Performance</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Priority</label>
                  <select class="form-control" name="priority">
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="low">Low</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Goal Title</label>
              <input type="text" class="form-control" name="title" required placeholder="e.g., Lose 10kg">
            </div>
            <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea class="form-control" name="description" rows="2" placeholder="Describe your goal..."></textarea>
            </div>
            <div class="row">
              <div class="col-md-4">
                <div class="mb-3">
                  <label class="form-label">Target Value</label>
                  <input type="number" class="form-control" name="target_value" step="0.1">
                </div>
              </div>
              <div class="col-md-4">
                <div class="mb-3">
                  <label class="form-label">Current Value</label>
                  <input type="number" class="form-control" name="current_value" step="0.1" value="0">
                </div>
              </div>
              <div class="col-md-4">
                <div class="mb-3">
                  <label class="form-label">Unit</label>
                  <input type="text" class="form-control" name="unit" placeholder="kg, reps, minutes">
                </div>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Target Date</label>
              <input type="date" class="form-control" name="target_date">
            </div>
            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary">Add Goal</button>
              <button type="button" class="btn btn-secondary" onclick="hideAddGoalForm()">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Additional JavaScript for enhanced modals

// Fitness Form Handler
document.getElementById('fitnessForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  
  fetch('<?php echo BASE_URL; ?>api/update_fitness_profile.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      bootstrap.Modal.getInstance(document.getElementById('fitnessModal')).hide();
      location.reload();
    } else {
      alert('Error updating fitness profile: ' + data.message);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('An error occurred while updating fitness profile');
  });
});

// Nutrition Form Handler
document.getElementById('nutritionForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  
  fetch('<?php echo BASE_URL; ?>api/update_nutrition_plan.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      bootstrap.Modal.getInstance(document.getElementById('nutritionModal')).hide();
      location.reload();
    } else {
      alert('Error updating nutrition plan: ' + data.message);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('An error occurred while updating nutrition plan');
  });
});

// Goals Management Functions
function showAddGoalForm() {
  document.getElementById('addGoalForm').style.display = 'block';
}

function hideAddGoalForm() {
  document.getElementById('addGoalForm').style.display = 'none';
  document.getElementById('newGoalForm').reset();
}

function updateGoalProgress(goalId) {
  const currentValue = prompt('Enter current progress value:');
  if (currentValue !== null && currentValue !== '') {
    const formData = new FormData();
    formData.append('goal_id', goalId);
    formData.append('current_value', currentValue);
    
    fetch('<?php echo BASE_URL; ?>api/update_goal_progress.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        location.reload();
      } else {
        alert('Error updating goal progress: ' + data.message);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('An error occurred while updating goal progress');
    });
  }
}

function deleteGoal(goalId) {
  if (confirm('Are you sure you want to delete this goal?')) {
    const formData = new FormData();
    formData.append('goal_id', goalId);
    
    fetch('<?php echo BASE_URL; ?>api/delete_goal.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        location.reload();
      } else {
        alert('Error deleting goal: ' + data.message);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('An error occurred while deleting goal');
    });
  }
}

// New Goal Form Handler
document.getElementById('newGoalForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  
  fetch('<?php echo BASE_URL; ?>api/create_goal.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      location.reload();
    } else {
      alert('Error creating goal: ' + data.message);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('An error occurred while creating goal');
  });
});

// Enhanced modal functions
function openFitnessModal() {
  new bootstrap.Modal(document.getElementById('fitnessModal')).show();
}

function openNutritionModal() {
  new bootstrap.Modal(document.getElementById('nutritionModal')).show();
}

function openGoalsModal() {
  new bootstrap.Modal(document.getElementById('goalsModal')).show();
}
</script>