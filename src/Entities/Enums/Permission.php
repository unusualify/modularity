<?php

namespace OoBook\CRM\Base\Entities\Enums;

enum Permission:string {
  case DASHBOARD = 'dashboard';
  case VIEW = 'view';
  case CREATE = 'create';
  case EDIT = 'edit';
  case DELETE = 'delete';
  case DESTROY = 'destroy';
  case DUPLICATE = 'duplicate';
  case REORDER = 'reorder';
}


