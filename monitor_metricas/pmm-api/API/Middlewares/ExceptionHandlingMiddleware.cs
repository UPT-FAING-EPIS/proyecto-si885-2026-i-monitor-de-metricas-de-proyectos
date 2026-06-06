using System.Net;
using System.Text.Json;
using ProjectMetricsMonitor.Application.Exceptions;

namespace ProjectMetricsMonitor.API.Middlewares;

public sealed class ExceptionHandlingMiddleware
{
    private readonly RequestDelegate _next;
    private readonly IHostEnvironment _env;
    private static readonly JsonSerializerOptions JsonOptions = new()
    {
        PropertyNamingPolicy = JsonNamingPolicy.CamelCase
    };

    public ExceptionHandlingMiddleware(RequestDelegate next, IHostEnvironment env)
    {
        _next = next;
        _env = env;
    }

    public async Task Invoke(HttpContext context)
    {
        try
        {
            await _next(context);
        }
        catch (Exception ex)
        {
            context.Response.ContentType = "application/problem+json";

            var (status, title, detail) = ex switch
            {
                HttpStatusCodeException app => (app.StatusCode, app.Title, app.Detail),
                _ => ((int)HttpStatusCode.InternalServerError, "Unexpected error", _env.IsDevelopment() ? ex.Message : null)
            };

            context.Response.StatusCode = status;

            var payload = new
            {
                type = $"https://httpstatuses.com/{status}",
                title,
                status,
                detail,
                traceId = context.TraceIdentifier
            };

            await context.Response.WriteAsync(JsonSerializer.Serialize(payload, JsonOptions));
        }
    }
}
