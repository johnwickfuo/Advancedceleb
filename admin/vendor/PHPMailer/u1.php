<div class="form-group">
                                            <label for="step_<?php echo $row['step_id']; ?>">
                                                <?php echo $row['step_name']; ?>
                                            </label>

                                            <input type="checkbox" 
                                                id="step_<?php echo $row['step_id']; ?>" 
                                                name="step_status_<?php echo $row['step_id']; ?>" 
                                                data-toggle="switch"
                                                <?php if ($row['step_status'] == 1) echo "checked"; ?>>

                                            <input type="hidden" name="step_id[]" value="<?php echo $row['step_id']; ?>">
                                            <textarea 
                                                name="step_description[]" 
                                                class="form-control mt-2" 
                                                placeholder="Enter step description"><?php echo $row['step_description']; ?></textarea>
                                        </div>