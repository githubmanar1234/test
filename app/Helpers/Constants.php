<?php


namespace App\Helpers;


class Constants
{
    // filter value
    const NULL = "null";
    const NOT_NULL = "not null";
    const PER_PAGE_DEFAULT = 10;

    const DESIGNER = "designer";
    const CLIENT = "client";
    const SUPERVISOR = "superVisor";
    const LIST_MODIFICATIONS = 'list modifications';
    const CONV_CAMEL = 'camel';
    const CONV_UNDERSCORE = 'underscore';
    const ORDER_BY = "order_by";
    const ORDER_By_DIRECTION = "order_by_direction";
    const FILTER_OPERATOR = "filter_operator";
    const FILTERS = "filters";
    const PER_PAGE = "per_page";
    const PAGINATE = "paginate";

    // languages requests

    const LANGUAGE_EN = "en";
    const LANGUAGES_AR = "ar";
    const LANGUAGES_FR = "fr";
    const LANGUAGES =
        [
            Constants::LANGUAGE_EN,
            Constants::LANGUAGES_AR,
            Constants::LANGUAGES_FR,
        ];
    const STATUS_REJECTED ='Rejected';
    const STATUS_ACCEPTED ='Accepted';
    const STATUS_UNDER_REVIEW ='Under review';
    const STATUS_UPDATE_REQUEST ='Update request';
    const STATUSES = [
        Constants::STATUS_REJECTED,
        Constants::STATUS_ACCEPTED,
        Constants::STATUS_UNDER_REVIEW,
    ];
    const NOTIFICATION_TYPE_KEY = "Type";
    const NOTIFICATION_TYPE_SERVICE = "Service";
    const NOTIFICATION_TYPE_JOB = "Job";
    const NOTIFICATION_TYPE_SERVICE_ORDER = "ServiceOrder";


    const ORDER_STATUS_REJECTED ='Rejected';
    const ORDER_STATUS_UNDER_REVIEW ='Under review';
    const ORDER_STATUS_COMPLETED ='Completed';
    const ORDER_STATUS_UNDERWAY ='Underway';
    const ORDERS_STATUSES = [
        Constants::STATUS_REJECTED,
        Constants::ORDER_STATUS_COMPLETED,
        Constants::ORDER_STATUS_UNDER_REVIEW,
        Constants::ORDER_STATUS_UNDERWAY,
    ];

    const NUMBER_OF_REPORTS_TO_DELETE_SERVICE = 15;
    const NUMBER_OF_REPORTS_TO_DELETE_JOB = 15;

    // Queues
    const QUEUE_SHORT_TIME_PROCESSES  = 'shortTimeProcesses';
    const QUEUE_LONG_TIME_PROCESSES  = 'longTimeProcesses';
    const TICKETS_QUEUE = 'tickets'; // for one thread



}