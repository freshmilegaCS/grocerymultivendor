<link rel="shortcut icon" href="<?= base_url($settings['logo']) ?>">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..90&display=swap" rel="stylesheet">
<!-- Tempusdominus Bootstrap 4 -->
<link rel="stylesheet" href="<?= base_url('/assets/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') ?>">
<!-- iCheck -->
<link rel="stylesheet" href="<?= base_url('/assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css') ?>">
<!-- JQVMap -->
<link rel="stylesheet" href="<?= base_url('/assets/plugins/jqvmap/jqvmap.min.css') ?>">
<!-- Theme style -->
<link rel="stylesheet" href="<?= base_url('/assets/dist/css/adminlte.min.css') ?>">
<!-- overlayScrollbars -->
<link rel="stylesheet" href="<?= base_url('/assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') ?>">
<!-- summernote -->
<link rel="stylesheet" href="<?= base_url('/assets/plugins/summernote/summernote-bs4.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('/assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('/assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('/assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('/assets/plugins/sweetalert2/sweetalert2.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('/assets/plugins/toastr/toastr.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('/assets/plugins/tag/tagsinput.css') ?>">
<link rel="stylesheet" href="<?= base_url('/assets/plugins/select2/css/select2.min.css') ?>">
<link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.6.0/uicons-thin-rounded/css/uicons-thin-rounded.css'>
<link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.6.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
<link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.6.0/uicons-bold-straight/css/uicons-bold-straight.css'>
<link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.6.0/uicons-thin-straight/css/uicons-thin-straight.css'>

<style>
  .dark-mode .select2-selection {
    background-color: #343a40 !important;
    border-color: #6c757d;
  }

  .content-wrapper {
    position: relative;
  }


  @media screen and (min-width: 1280px) {
    .content-wrapper:before {
      content: ' ';
      display: block;
      position: absolute;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      opacity: 0.2;
      background-repeat: no-repeat;
      background-position: center;
      background-size: cover;
    }
  }

  @media screen and (max-width: 480px) {
    .content-wrapper:before {
      content: ' ';
      display: flex;
      position: absolute;
      left: 0;
      top: 171px;
      width: 100%;
      height: 30%;
      opacity: 0.2;
      background-repeat: no-repeat;
      background-position: center;
      background-size: cover;
    }
  }

  .text-sm .main-header .nav-link>.fi::before {
    font-size: 15px;
  }

  .text-underline {
    text-decoration: underline;
  }

  .content {
    position: relative;
  }



  /* Pagination container */
  .dataTables_wrapper .dataTables_paginate {
    padding-top: 10px;
  }

  /* Pagination button styles */
  .dataTables_wrapper .dataTables_paginate .paginate_button {
    padding: 8px 12px;
    margin: 0 4px;
    border-radius: 8px;
    border: 1px solid #00897B;
    /* Light border for each button */
    background-color: #ffffff;
    color: #374151;
    /* Dark gray text */
    font-size: 14px;
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
  }

  .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background-color: #f3f4f6;
    /* Light hover background */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    /* Subtle shadow on hover */
  }

  /* Active/current pagination button */
  .dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background-color: #f0f5ff;
    /* Light blue background for active page */
    border-color: #2563eb;
    /* Blue border for active button */
    color: #2563eb;
    /* Blue text for active button */
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.3);
    /* Subtle blue focus ring */
  }

  /* Disabled pagination buttons (prev/next on edge cases) */
  .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
    background-color: #f9fafb;
    border-color: #00897B;
    color: #9ca3af;
    cursor: not-allowed;
  }

  /* Pagination ellipsis */
  .dataTables_wrapper .dataTables_paginate .ellipsis {
    padding: 8px 16px;
    margin: 0 4px;
    color: #9ca3af;
  }

  /* Custom styling for prev/next arrows */


  .dataTables_wrapper .dataTables_paginate .paginate_button.previous:before {
    font-family: uicons-bold-rounded !important;
    font-style: normal;
    font-weight: normal !important;
    font-variant: normal;
    text-transform: none;
    line-height: 1;
    -webkit-font-smoothing: antialiased;
    content: "\e0c6";
  }

  .dataTables_wrapper .dataTables_paginate .paginate_button.next:before {
    font-family: uicons-bold-rounded !important;
    font-style: normal;
    font-weight: normal !important;
    font-variant: normal;
    text-transform: none;
    line-height: 1;
    -webkit-font-smoothing: antialiased;
    content: "\e0cc";

  }

  .page-item.disabled .page-link,
  .page-item:first-child .page-link,
  .page-item:last-child .page-link {
    display: none;
  }

  .dataTables_wrapper .dataTables_filter {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    margin-bottom: 10px;
  }

  .dataTables_wrapper .dt-buttons {
    margin-right: 10px;
  }

  .dt-button {
    padding: 8px 14px;
    margin-right: 8px;
    border-radius: 6px;
    background-color: #2563eb;
    color: white;
    border: none;
    font-size: 14px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
  }

  .dt-button:hover {
    background-color: #1d4ed8;
    transform: translateY(-2px);
  }

  .dt-button:active {
    background-color: #1e40af;
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .dataTables_wrapper .dataTables_filter input {
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    width: 200px;
    margin-left: 10px;
    transition: border-color 0.3s ease;
  }

  /* Search box input on focus */
  .dataTables_wrapper .dataTables_filter input:focus {
    border-color: #2563eb;
    /* Blue border on focus */
    outline: none;
  }

  .dropdown-menu {
    min-width: 120px;
    /* Adjust as needed */
  }

  .btn-group .dropdown-menu:focus {
    display: block;
  }

  .primary-bprder,
  .primary-bprder:focus,
  .primary-bprder:hover {
    border-color: #00897B;
  }

  .main-footer {
    text-align: center;
  }

  .dataTables_filter {
    display: none !important;
  }

  .sidebar-search {
    position: fixed;
    z-index: 99;
    width: 235px
  }

  ::-webkit-scrollbar {
    width: 8px;
    height: 12px;
  }

  ::-webkit-scrollbar-thumb {
    background: linear-gradient(45deg, #00897B, #8bc34a);
    border-radius: 10px;
    border: 2px solid #f0f0f0;
    transition: background 0.3s ease;
  }


  ::-webkit-scrollbar-track {
    background: #e0e0e0;
    border-radius: 10px;
    margin: 2px;
  }

  html {
    scroll-behavior: smooth;
  }

  .nav-dropdowm-item {
    padding: .8rem 1.5rem !important;
  }

  .nav-link {
    display: flex;
    align-items: center;
  }

  .nav-link i {
    font-size: 1.2rem;
    line-height: 1;
    margin-right: 5px;
  }

  .nav-link span {
    line-height: 1;
    /* Ensures text aligns vertically with the icon */
  }

  .navbar-nav .nav-item {
    margin-inline: 5px !important;
  }

  .permission-not-allowed {
    width: 500px;
    height: 500px;
    margin-right: auto;
    margin-left: auto;
    display: block;
  }

  .go-back-btn {
    margin-right: auto;
    margin-left: auto;
    display: block;
  }

  #product-list li {
    margin: 10px 0;
    padding: 15px;
    background-color: #f4f4f9;
    border: 1px solid #ddd;
    border-radius: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: grab;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    transition: background-color 0.3s ease;
  }

  #product-list li:hover {
    background-color: #eaeaff;
  }

  #product-list li:active {
    cursor: grabbing;
    background-color: #cfd8ff;
  }

  /* Draggable icon */
  .drag-handle {
    font-size: 20px;
    color: #888;
    cursor: grab;
  }

  #map {
    height: 300px;
  }

  #description {
    font-family: Roboto;
    font-size: 15px;
    font-weight: 300;
  }

  #infowindow-content .title {
    font-weight: bold;
  }

  #infowindow-content {
    display: none;
  }

  #map #infowindow-content {
    display: inline;
  }

  .pac-card {
    background-color: #fff;
    border: 0;
    border-radius: 2px;
    box-shadow: 0 1px 4px -1px rgba(0, 0, 0, 0.3);
    margin: 10px;
    padding: 0 0.5em;
    font: 400 18px Roboto, Arial, sans-serif;
    overflow: hidden;
    font-family: Roboto;
    padding: 0;
  }

  #pac-container {
    padding-bottom: 12px;
    margin-right: 12px;
  }

  .pac-controls {
    display: inline-block;
    padding: 5px 11px;
  }

  .pac-controls label {
    font-family: Roboto;
    font-size: 13px;
    font-weight: 300;
  }





  #target {
    width: 345px;
  }

  .custom-dropzone {
    align-items: center;
    justify-content: center;
    background-color: #f0f0f0 !important;
    border: 2px dashed #cccccc !important;
    border-radius: 5px;
    color: #333;
    padding: 20px;
    font-size: 20px;
    position: relative;
    cursor: pointer;
    text-align: center;
  }

  .dropzone-clickable-area {
    text-align: center;
  }

  .dropzone-clickable-area .icon {
    font-size: 50px;
    color: #888;
  }

  /* Hide default Dropzone message */
  .dropzone .dz-message {
    display: none;
  }

  /* Custom preview for uploaded files */
  .custom-preview .dz-preview .dz-image img {
    width: 100px;
    height: auto;
  }

  .select2-container--default .select2-selection--multiple {
    border: 0;
    background-color: rgba(150, 150, 150, 0.1);
  }

  .select2-container--default.select2-container--focus .select2-selection--multiple {
    border: 0px;
  }

  .spin-icon {
    display: inline-block;
    animation: spin 1s linear infinite;
  }

  @keyframes spin {
    0% {
      transform: rotate(0deg);
    }

    100% {
      transform: rotate(360deg);
    }
  }

  .bb-1 {
    border-bottom: 1px solid #ccc;
    margin-bottom: 5px;
  }

  .btn-ai {
    font-size: 14px;
    font-weight: bold;
    color: white;
    padding: 5px 5px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    background: linear-gradient(45deg, #ff416c, #ff4b2b, #ff9a44, #2162ef);
    background-size: 400% 400%;
    animation: gradientAnimation 3s ease infinite;
  }

  @keyframes gradientAnimation {
    0% {
      background-position: 0% 50%;
    }

    50% {
      background-position: 100% 50%;
    }

    100% {
      background-position: 0% 50%;
    }
  }

  #avgOrderValueGauge {
    width: 100%;
  }

  .custom-form-control {
    border: 1px solid;
  }

  .nav-pills .nav-link {
    border-radius: 0;
    text-align: left;
  }

  .nav-pills .nav-link.active {
    background-color: #00897B;
    color: white;
  }

  .list-group-item {
    border: 1px solid #00897B;
    border-radius: 5px;
    background-color: #fff;
    color: #495057;
    padding: 11px 2px;
    text-align: center;
    font-size: 14px;
    margin-bottom: 13px;
  }

  .list-group-item:hover {
    background-color: #e9ecef;
    color: #000;
  }

  .list-group-item.active {
    background-color: #00897B;
    color: #fff;
    border-radius: 5px;
    border: 1px solid #00897B;
  }

  .tab-content {
    padding: 15px;
    background-color: white;
  }
</style>