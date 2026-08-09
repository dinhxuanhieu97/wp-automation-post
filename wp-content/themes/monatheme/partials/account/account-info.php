<?php
get_template_part('partials/account/account-header', null, ['tab' => $args['tab']]);
?>
<?php
$current_userid = $args['current_userid'];
$current_userdata = $args['current_userdata'];
$mona_user_name                   =     get_field('mona_user_name',   'user_' . $current_userid);
$mona_user_bod                   =     get_field('mona_user_bod',   'user_' . $current_userid);
$mona_user_phone                   =     get_field('mona_user_tel',   'user_' . $current_userid);
$mona_user_gender               =     get_field('mona_user_gender',   'user_' . $current_userid);
$mona_user_country              =     get_field('mona_user_country',   'user_' . $current_userid);
$mona_user_address             =     get_field('mona_user_address',   'user_' . $current_userid);
?>
<div class="info">
    <form id="formUser">
        <div class="info_wrap">
            <div class="info_list d-wrap">
                <div class="info_item d-item d-2">
                    <div class="form-ip">
                        <div class="form-ip-label">
                            <label for="hName"><?php echo __('Họ', 'monamedia'); ?></label>
                        </div>
                        <div class="form-ip-ip">
                            <input id="hName" name="user_last_name" type="text" placeholder="<?php echo  __('Họ của bạn', 'monamedia') ?>" value="<?php echo $current_userdata->user_lastname; ?>">
                        </div>
                    </div>
                </div>
                <div class="info_item d-item d-2">
                    <div class="form-ip">
                        <div class="form-ip-label">
                            <label for="tName"><?php echo __('Tên', 'monamedia'); ?></label>
                        </div>
                        <div class="form-ip-ip">
                            <input id="tName" name="user_first_name" type="text" placeholder="<?php echo  __('Tên của bạn', 'monamedia') ?>" value="<?php echo $current_userdata->user_firstname; ?>">
                        </div>
                    </div>
                </div>
                <div class="info_item d-item d-2">
                    <div class="form-ip">
                        <div class="form-ip-label">
                            <label for="email"><?php echo __('Email', 'monamedia'); ?></label>
                        </div>
                        <div class="form-ip-ip">
                            <input id="email" type="text" name="user_email" readonly value="<?php echo $current_userdata->user_email; ?>" placeholder="<?php echo  __('Email của bạn', 'monamedia') ?>">
                            <div class="mona-error mona-error-user-email"></div>
                        </div>
                    </div>
                </div>
                <div class="info_item d-item d-2">
                    <div class="form-ip">
                        <div class="form-ip-label">
                            <label for="phone"><?php echo __('Số điện thoại', 'monamedia'); ?></label>
                        </div>
                        <div class="form-ip-ip">
                            <input id="phone" type="tel" placeholder="<?php echo  __('Số điện thoại của bạn', 'monamedia') ?>" name="user_phone" value="<?php echo $mona_user_phone; ?>">
                            <div class="mona-error mona-error-user-phone"></div>
                        </div>
                    </div>
                </div>
                <div class="info_item d-item d-2">
                    <div class="form-ip">
                        <div class="form-ip-label">
                            <label for="fileDate"><?php echo __('Ngày sinh', 'monamedia'); ?></label>
                        </div>
                        <div class="form-ip-ip choose-date">
                            <input class="date" id="datepicker" type="text" name="user_bod" value="<?php echo $mona_user_bod; ?>" placeholder="<?php echo  __('Ngày sinh của bạn', 'monamedia') ?>">
                            <div class="mona-error mona-error-user-bod"></div>
                        </div>
                    </div>
                </div>
                <div class="info_item d-item d-2">
                    <div class="form-ip">
                        <div class="form-ip-label">
                            <label for="nation"><?php echo __('Quốc gia', 'monamedia'); ?></label>
                        </div>
                        <select class="form-control" id="countries" name="user_country">
                            <?php
                            $json_file_path = get_template_directory() . '/countries.json';
                            $json_data = file_get_contents($json_file_path);
                            $repos_data = json_decode($json_data, true);
                            usort($repos_data['data'], function ($a, $b) {
                                return strcmp($a['name'], $b['name']);
                            });
                            $box = $repos_data['data'];
                            if (content_exists($box)) {
                                foreach ($box as $item) {
                                    $selected = $mona_user_country == $item['name'] ? 'selected' : '';
                                    echo '<option value=' . $item['name'] . ' data-value="' . $item['iso2'] . '" ' . $selected . '>' . $item['name'] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="info_item d-item d-2">
                    <div class="form-ip">
                        <div class="form-ip-label">
                            <label for="address"><?php echo __('Địa chỉ', 'monamedia'); ?></label>
                        </div>
                        <div class="form-ip-ip">
                            <input id="address" name="user_address" type="text" placeholder="<?php echo  __('Thông tin địa chỉ của bạn', 'monamedia') ?>" value="<?php echo $mona_user_address; ?>">
                        </div>
                    </div>
                </div>
                <div class="info_item d-item d-2">
                    <div class="form-ip">
                        <div class="form-ip-label">
                            <label for="address"><?php echo __('Giới tính', 'monamedia'); ?></label>
                        </div>
                        <div class="gender">
                            <div class="gender-list">
                                <label class="gender-item">
                                    <input type="radio" <?php echo $mona_user_gender == 'male' ? 'checked' : ''; ?> name="user_gender" value="male"><span class="box"></span>
                                    <p class="note-text"><?php echo __('Nam', 'monamedia'); ?></p>
                                </label>
                                <label class="gender-item">
                                    <input type="radio" <?php echo $mona_user_gender == 'female' ? 'checked' : ''; ?> name="user_gender" value="female"><span class="box"></span>
                                    <p class="note-text"><?php echo __('Nữ', 'monamedia'); ?></p>
                                </label>
                                <label class="gender-item">
                                    <input type="radio" <?php echo $mona_user_gender == 'other' ? 'checked' : ''; ?> name="user_gender" value="other"><span class="box"></span>
                                    <p class="note-text"><?php echo __('Khác', 'monamedia'); ?></p>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="info_btn">
                <button class="btn cl-2 deactive" type="submit"> <span class="btn-text"><?php echo __('Lưu thay đổi', 'monamedia'); ?></span>
                </button>
                <button class="btn trans" id="clearBtn"> <span class="btn-text"><?php echo __('Hủy bỏ', 'monamedia'); ?></span>
                </button>
            </div>
        </div>
    </form>
</div>