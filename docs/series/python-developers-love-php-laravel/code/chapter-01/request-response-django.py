# filename: views.py
"""
Django Request/Response Examples

This demonstrates Django's HttpRequest and HttpResponse objects.
"""

from django.http import HttpResponse, JsonResponse, HttpRequest

def my_view(request: HttpRequest):
    # Access request data
    method = request.method  # 'GET', 'POST', etc.
    user_id = request.GET.get('id')  # Query parameter
    data = request.POST.get('data')  # Form data
    path = request.path  # '/example'
    headers = request.headers  # Request headers
    
    # Create simple response
    return HttpResponse('Hello, World!', status=200)
    
    # Create JSON response
    return JsonResponse({'key': 'value', 'user_id': user_id})
    
    # Response with custom headers
    response = HttpResponse('Hello')
    response['X-Custom-Header'] = 'value'
    return response

