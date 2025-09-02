<aside class="main-sidebar <?php if ($settings['thememode'] == 'Light') {
                                echo "sidebar-light-" . $settings['primary_color'];
                            } else {
                                echo "sidebar-dark-" . $settings['primary_color'];
                            } ?>  " id="sidebar">
    <!-- Brand Logo -->
    <a href="/admin/dashboard" class="brand-link <?php if ($settings['thememode'] == 'Light') {
                                                        echo "bg-light bg-white";
                                                    } else {
                                                        echo "bg-dark bg-white";
                                                    } ?>">
        <?php if ($settings['logo'] != null) {
        ?>
            <img src="<?= base_url($settings['logo']) ?>" alt="<?= $settings['business_name']; ?>" class="brand-image img-circle elevation-3" style="background-color: white;">

        <?php
        } ?>
        <span class="brand-text font-weight-light"><?= $settings['business_name']; ?></span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">

        <!-- Sidebar Menu -->
        <div class=" sidebar-search">
            <div class="form-inline mt-2">
                <div class="input-group" data-widget="sidebar-search">
                    <input class="form-control form-control-sidebar" id="search-menu-input" type="search" placeholder="Search Menu Ctrl + F" aria-label="Search">
                    <div class="input-group-append">
                        <button class="btn btn-sidebar">
                            <i class="fi fi-tr-magnifying-glass-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <nav class="mt-5">
            <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent text-sm" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item has-treeview mt-2"><a href="/admin/dashboard" class="nav-link"><i class="nav-icon fi fi-tr-dashboard-monitor"></i>
                        <p>Dashboard</p>
                    </a></li>
                <?php
                $sidebarItems = get_admin_sidebar_data();
                $parents = [];
                $children = [];

                foreach ($sidebarItems as $item) {
                    if ($item['parent_id'] == 0) {
                        $parents[] = $item;
                    } else {
                        $children[$item['parent_id']][] = $item;
                    }
                }

                function render_sidebar_item($item)
                {
                    if (can_view($item['permission_category_short_code'])) {
                        echo '<li class="nav-item">';
                        echo '<a href="' . esc($item['url']) . '" class="nav-link">';
                        if (!empty($item['icon'])) {
                            echo '<i class="nav-icon ' . esc($item['icon']) . '"></i>';
                        }
                        if ($item['is_it_have_badge']) {
                            echo '<p>' . esc($item['title']) . ' <span class="right badge ' . esc($item['badge_type']) . '">' . $item['badge_function']($item['badge_function_parameter']) . '</span></p>';
                        } else {
                            echo '<p>' . esc($item['title']) . '</p>';
                        }
                        echo '</a>';
                        echo '</li>';
                    }
                }

                foreach ($parents as $parent) {
                    if ($parent['is_it_header'] == 1) {
                        // Render header
                        echo '<li class="nav-header mt-2">' . esc($parent['title']) . '</li>';
                    } else {
                        if (can_view($parent['permission_category_short_code'])) {
                            echo '<li class="nav-item has-treeview ' . $parent['permission_category_short_code'] . '">'; // Mark this li as having children
                            echo '<a href="' . esc($parent['url']) . '" class="nav-link">';
                            if (!empty($parent['icon'])) {
                                echo '<i class="nav-icon ' . esc($parent['icon']) . '"></i>';
                            }
                            if (isset($children[$parent['id']])) {
                                $title =  '<p>' . esc($parent['title']);

                                echo $title .=  '<i class="right fi fi-tr-angle-small-right"></i></p>';
                            } else {
                                echo '<p>' . esc($parent['title']) . '</p>';
                            }
                            echo '</a>';

                            if (isset($children[$parent['id']])) {
                                echo '<ul class="nav nav-treeview">'; // Start child menu
                                foreach ($children[$parent['id']] as $child) {
                                    render_sidebar_item($child, true); // Render each child with 'nav-treeview' class
                                }
                                echo '</ul>'; // End child menu
                            }
                            echo '</li>'; // Close parent li
                        }
                    }
                }
                ?>

            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>