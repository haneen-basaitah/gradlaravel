<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index3.html" class="brand-link">
      <img src="{{asset('dashboard/dist/img/AdminLTELogo.png')}}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">MediMind</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="{{asset('dashboard/dist/img/user2-160x160.jpg')}}" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block">Alexander Pierce</a>
        </div>
      </div>

      <!-- SidebarSearch Form -->
      <div class="form-inline">
        <div class="input-group" data-widget="sidebar-search">
          <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
          <div class="input-group-append">
            <button class="btn btn-sidebar">
              <i class="fas fa-search fa-fw"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
          <li class="nav-item">
            <a href="{{ route('dashboard.index') }}" @class(['nav-link', 'active' => request()->routeIs('dashboard.index')])>
              <i class="nav-icon fas fa-th"></i>
              <p>
                Dashboard

              </p>
            </a>
          </li>
     <!-- Patients Management -->
     <li class="nav-item has-treeview">
        <a href="" class="nav-link ">
            <i class="nav-icon fas fa-user"></i>
            <p>
                Elderly Management
                <i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="{{ route('patients.add') }}" @class(['nav-link', 'active' => request()->routeIs('patients.add')])>
                    <i class="far fa-circle nav-icon"></i>
                    <p>Add Elderly</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('patients.view') }}" @class(['nav-link', 'active' => request()->routeIs('patients.view')])>
                    <i class="far fa-circle nav-icon"></i>
                    <p>View Elderly</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('patients.edit') }}" @class(['nav-link', 'active' => request()->routeIs('patients.edit')])>
                    <i class="far fa-circle nav-icon"></i>
                    <p>Edit Elderly Information</p>
                </a>
            </li>

        </ul>
    </li>
      <li class="nav-item has-treeview">
        <a href="{{ route('dashboard.index') }}" @class(['nav-link', 'active' => request()->routeIs('')])>
            <i class="fas fa-pills nav-icon"></i>
            <p>
            Medications Schedule
            <i class="right fas fa-angle-left"></i>
          </p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="{{ route('medications.add') }}" @class(['nav-link', 'active' => request()->routeIs('')])>
                    <i class="far fa-circle nav-icon"></i>
                    <p> Add Medications</p>
                </a>

                <li class="nav-item">
                    <a href="{{ route('medications.view') }}" @class(['nav-link', 'active' => request()->routeIs('')])>
                        <i class="far fa-circle nav-icon"></i>
                        <p>View Medications</p>
                    </a>
                </li>
            </li>

        </ul>

      </li>

      <li class="nav-item">
        <a href="{{ route('dashboard.index') }}" @class(['nav-link', 'active' => request()->routeIs('')])>
            <i class="fas fa-running nav-icon"></i>
          <p>
            Activety Schedule
            <i class="right fas fa-angle-left"></i>

          </p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                {{-- <a href="{{ route('activities.add') }}" @class(['nav-link', 'active' => request()->routeIs('')])>
                    <i class="far fa-circle nav-icon"></i>
                    <p> Add Activety</p>
                </a> --}}

                <li class="nav-item">
                    <a href="{{ route('activities.view') }}" @class(['nav-link', 'active' => request()->routeIs('')])>
                        <i class="far fa-circle nav-icon"></i>
                        <p>Activety Report </p>
                    </a>
                </li>
            </li>

        </ul>
      </li>

      <li class="nav-item">
        <a href="{{ route('dashboard.index') }}" @class(['nav-link', 'active' => request()->routeIs('')])>
            <i class="fas fa-warehouse"></i>
          <p>
            Closet Statues
            <i class="right fas fa-angle-left"></i>
          </p>
        </a>
        <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('closets.view') }}" @class(['nav-link', 'active' => request()->routeIs('')])>
                        <i class="far fa-circle nav-icon"></i>
                        <p>View Statues </p>
                    </a>
                </li>
            </li>

        </ul>

      </li>
      <li class="nav-item">
        <a href="{{ route('dashboard.index') }}" @class(['nav-link', 'active' => request()->routeIs('')])>
          <i class="nav-icon fas fa-th"></i>
          <p>
            Report

          </p>
        </a>
      </li>
</ul>
</nav>
<!-- /.sidebar-menu -->
</div>
<!-- /.sidebar -->
</aside>
