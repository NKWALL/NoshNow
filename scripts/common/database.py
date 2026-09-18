"""Shared database configuration for NoshNow utility scripts."""

from __future__ import annotations

import os

import mysql.connector


def connect():
    """Create a MySQL connection from environment variables."""
    configuration = dict(
        host=os.getenv("NOSHNOW_DB_HOST", "127.0.0.1"),
        port=int(os.getenv("NOSHNOW_DB_PORT", "3306")),
        database=os.getenv("NOSHNOW_DB_NAME", "noshnow"),
        user=os.getenv("NOSHNOW_DB_USER", "noshnow_app"),
        password=os.getenv("NOSHNOW_DB_PASSWORD", ""),
        charset="utf8mb4",
    )
    socket = os.getenv("NOSHNOW_DB_SOCKET")
    if socket:
        configuration["unix_socket"] = socket
    return mysql.connector.connect(**configuration)
