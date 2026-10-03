<div class="tab-pane fade" id="otp-management" role="tabpanel" aria-labelledby="otp-management-tab" style="margin-top: 30px;">
                                <form method="post" action="">
                                    <div class="form-group">
                                        <label for="otp_status">Enable / Disable PIN for Customers</label>
                                        <input type="checkbox" id="otp_status" name="otp_status" data-toggle="switch" <?php if ($otp_enabled == 1) echo "checked"; ?>>
                                    </div>
                                    <button type="submit" name="update_otp" class="btn btn-primary mt-2">Save PIN Settings</button>
                                </form>
                            </div>